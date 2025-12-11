<?php

declare(strict_types=1);

namespace ORMBundle\Validator\Constraints;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ORMUniqueValidator extends ConstraintValidator
{
    private const ALIAS = 'entity';
    private const EQ_PATTERN = '%s.%s = :%s';
    private const NEQ_PATTERN = '%s.%s <> :%s';

    private const ALLOWED_TYPES = [
        Types::INTEGER,
        Types::STRING,
        Types::BOOLEAN,
        Types::ARRAY,
    ];

    private ManagerRegistry $registry;

    private PropertyAccessorInterface $accessor;

    public function __construct(ManagerRegistry $registry, PropertyAccessorInterface $accessor)
    {
        $this->registry = $registry;
        $this->accessor = $accessor;
    }

    /**
     * @throws NonUniqueResultException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ORMUnique) {
            throw new UnexpectedTypeException($constraint, ORMUnique::class);
        }

        $entityManager = $this->registry->getManagerForClass($constraint->entityClass);
        if (!$entityManager) {
            throw new \RuntimeException('No entity manager found for class '.$constraint->entityClass);
        }
        /** @var ClassMetadata $class */
        $class = $entityManager->getClassMetadata($constraint->entityClass);

        /** @var EntityRepository $repository */
        $repository = $entityManager->getRepository($constraint->entityClass);
        $queryBuilder = $repository->createQueryBuilder(self::ALIAS);

        $this->addConditions($queryBuilder, $class, $value, $constraint->includeFields, self::EQ_PATTERN);
        $this->addConditions($queryBuilder, $class, $value, $constraint->excludeFields, self::NEQ_PATTERN);

        if ($queryBuilder->getParameters()->isEmpty()) {
            return;
        }

        try {
            $entity = $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $ex) {
            $violationBuilder = $this->context->buildViolation($constraint->message);
            if ($constraint->path) {
                $violationBuilder->atPath($constraint->path);
            }
            $violationBuilder->addViolation();

            return;
        }

        if (
            null !== $constraint->entityClass
            && $entity instanceof $constraint->entityClass
            && !$this->isTheSameEntity($constraint->identityField, $value, $entity)
        ) {
            $violationBuilder = $this->context->buildViolation($constraint->message);
            if ($constraint->path) {
                $violationBuilder->atPath($constraint->path);
            }
            $violationBuilder->addViolation();
        }
    }

    private function addConditions(
        QueryBuilder $queryBuilder,
        ClassMetadata $class,
        object $dto,
        array $fields,
        string $pattern,
    ): void {
        foreach ($fields as $dtoField => $entityField) {
            if (!is_string($dtoField)) {
                $dtoField = $entityField;
            }
            $dtoValue = $this->accessor->getValue($dto, $dtoField);
            if ((null === $dtoValue) || !$class->hasField($entityField)) {
                continue;
            }
            $fieldType = $class->getFieldMapping($entityField)['type'];
            if (!in_array($fieldType, self::ALLOWED_TYPES)) {
                $this->context
                    ->buildViolation('Field type not supported for uniqueness validation')
                    ->atPath($dtoField)
                    ->setCode(Type::INVALID_TYPE_ERROR)
                    ->addViolation()
                ;
            }
            $parameterKey = \sprintf('p_%s', $queryBuilder->getParameters()->count());
            $queryBuilder
                ->andWhere(
                    sprintf($pattern, self::ALIAS, $entityField, $parameterKey),
                )
                ->setParameter($parameterKey, $dtoValue, $fieldType)
            ;
        }
    }

    private function isTheSameEntity(?string $identityField, $dto, $entity): bool
    {
        return null !== $identityField && (
            (string) $this->accessor->getValue($entity, $identityField) ===
                (string) $this->accessor->getValue($dto, $identityField)
        );
    }
}
