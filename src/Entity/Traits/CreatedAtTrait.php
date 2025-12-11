<?php

declare(strict_types=1);

namespace ORMBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\ClockAwareTrait;

trait CreatedAtTrait
{
    use ClockAwareTrait;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, updatable: false)]
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", updatable=false)
     */
    private $createdAt;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    /**
     * @ORM\PrePersist()
     */
    public function initializeCreatedAt(): void
    {
        if (null === $this->createdAt) {
            $this->createdAt = $this->now();
        }
    }
}
