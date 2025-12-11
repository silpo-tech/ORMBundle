<?php

declare(strict_types=1);

namespace ORMBundle\Doctrine;

use Doctrine\DBAL\Connections\PrimaryReadReplicaConnection;

class PrimaryReadReplicaConnectionWrapper extends PrimaryReadReplicaConnection
{
    use ReconnectTrait;
}
