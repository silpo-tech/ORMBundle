<?php

declare(strict_types=1);

namespace ORMBundle\Doctrine;

use Doctrine\DBAL\Connection;

class ConnectionWrapper extends Connection
{
    use ReconnectTrait;
}
