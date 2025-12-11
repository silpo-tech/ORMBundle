<?php

declare(strict_types=1);

namespace ORMBundle\Validator\Constraints;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ORMExistsValidator extends ConstraintValidator
{
    private const ALIAS = 'entity';

    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string|bool|int|array|null $value
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ORMExists) {
            throw new UnexpectedTypeException($constraint, ORMExists::class);
        }

        if (null === $constraint->searchField || null === $constraint->entityClass || null === $constraint->fieldType) {
            throw new \InvalidArgumentException('All parameters should be present in constaint');
        }

        if (null === $value) {
            return;
        }

        $entityManager = $this->registry->getManagerForClass($constraint->entityClass);

        if (!$entityManager) {
            throw new \RuntimeException('No entity manager found for class '.$constraint->entityClass);
        }

        if ($this->checkTypeViolations($value, $constraint->fieldType)) {
            return;
        }

        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository($constraint->entityClass);

        if (is_array($value)) {
            $this->validateAllExists($value, $constraint, $repository);
        } else {
            $this->validateOneExists($value, $constraint, $repository);
        }
    }

    /**
     * @param string|bool|int $value
     */
    private function validateOneExists($value, ORMExists $constraint, EntityRepository $repository): void
    {
        $existingValues = $this->getExistingValues($constraint->searchField, [$value], $repository);

        if (0 === count($existingValues)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function validateAllExists(array $inputValues, ORMExists $constraint, EntityRepository $repository): void
    {
        $uniqueInputValues = array_unique($inputValues);

        $existingValues = $this->getExistingValues($constraint->searchField, $uniqueInputValues, $repository);

        if (count($existingValues) === count($uniqueInputValues)) {
            return;
        }

        foreach ($inputValues as $key => $val) {
            if (!in_array($val, $existingValues)) {
                $this->context->buildViolation($constraint->message)->atPath("[$key]")->addViolation();
            }
        }
    }

    /**
     * @return array<string|bool|int>
     */
    private function getExistingValues(string $fieldName, array $values, EntityRepository $repository): array
    {
        if (0 === count($values)) {
            return [];
        }

        /** @var QueryBuilder $qb */
        $qb = $repository->createQueryBuilder(self::ALIAS);
        $qb->select(self::ALIAS.'.'.$fieldName)->where(
            $qb->expr()->in(self::ALIAS.'.'.$fieldName, $values),
        );

        return array_column($qb->getQuery()->getScalarResult(), $fieldName);
    }

    /**
     * Before accessing database we need to check that values are the same type as in column.
     *
     * @param array|string|int|bool $value
     *
     * @return bool - returns tru if there are violations
     */
    private function checkTypeViolations($value, string $fieldType): bool
    {
        $notNull = new NotNull();
        $type = new Type($fieldType);

        $validator = $this->context->getValidator();

        if (is_array($value)) {
            $constraint = new All([$notNull, $type]);
        } else {
            $constraint = new Sequentially([$notNull, $type]);
        }

        $violations = $validator->validate($value, $constraint);

        if (0 === count($violations)) {
            return false;
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $this->context->buildViolation($violation->getMessage())
                ->atPath($violation->getPropertyPath())
                ->setCode(Type::INVALID_TYPE_ERROR)
                ->addViolation()
            ;
        }

        return true;
    }
}
