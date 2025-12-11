<?php

declare(strict_types=1);

namespace ORMBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Symfony\Component\Uid\Uuid;

class Uuid4Generator extends AbstractIdGenerator
{
    public function generateId(EntityManagerInterface $em, $entity): Uuid
    {
        $metadata = $em->getClassMetadata($entity::class);
        $idValues = $metadata->getIdentifierValues($entity);

        if (1 === count($idValues)) {
            return reset($idValues);
        }

        return Uuid::v4();
    }
}
