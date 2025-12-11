<?php

declare(strict_types=1);

namespace ORMBundle\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait VersionTrait
{
    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    /**
     * @var int
     *
     * @ORM\Version
     *
     * @ORM\Column(type="integer")
     */
    private $version;

    public function getVersion(): int
    {
        return $this->version;
    }
}
