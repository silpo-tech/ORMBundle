<?php

declare(strict_types=1);

namespace ORMBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use ORMBundle\Generator\Uuid6Generator;
use Symfony\Component\Uid\Uuid;

trait UuidIdTrait
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: Uuid6Generator::class)]
    /**
     * @var Uuid|null
     *
     * @ORM\Id
     *
     * @ORM\Column(type="uuid", unique=true)
     *
     * @ORM\GeneratedValue(strategy="CUSTOM")
     *
     * @ORM\CustomIdGenerator(class="ORMBundle\Generator\Uuid6Generator")
     */
    private $id;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        if ($this->id instanceof Uuid) {
            return $this;
        }

        $this->id = Uuid::fromString($id);

        return $this;
    }
}
