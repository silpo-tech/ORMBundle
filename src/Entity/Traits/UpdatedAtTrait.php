<?php

declare(strict_types=1);

namespace ORMBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\ClockAwareTrait;

trait UpdatedAtTrait
{
    use ClockAwareTrait;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    /**
     * @ORM\PrePersist()
     */
    public function prePersistUpdateAt(): void
    {
        if (null === $this->getUpdatedAt()) {
            $this->setUpdatedAt($this->now());
        }
    }

    #[ORM\PreUpdate]
    /**
     * @ORM\PreUpdate()
     */
    public function preUpdateUpdateAt(): void
    {
        $this->setUpdatedAt($this->now());
    }
}
