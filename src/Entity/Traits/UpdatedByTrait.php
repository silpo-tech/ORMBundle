<?php

declare(strict_types=1);

namespace ORMBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait UpdatedByTrait
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $updatedBy;

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    /**
     * @return $this
     */
    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
