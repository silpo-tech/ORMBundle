<?php

declare(strict_types=1);

namespace ORMBundle\Tests\Validator\Constraints;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

trait DBValidatorTestTrait
{
    /** @var ObjectProphecy<ManagerRegistry> */
    protected ObjectProphecy $registryMock;
    /** @var ObjectProphecy<EntityManagerInterface> */
    protected ObjectProphecy $emMock;
    /** @var ObjectProphecy<EntityRepository> */
    protected ObjectProphecy $repositoryMock;
    /** @var ObjectProphecy<Query> */
    protected ObjectProphecy $queryMock;
    /** @var ObjectProphecy<QueryBuilder> */
    protected ObjectProphecy $qbMock;

    protected function createBasicDoctrineMocks(): array
    {
        $this->registryMock = $this->prophesize(ManagerRegistry::class);
        $this->emMock = $this->prophesize(EntityManagerInterface::class);
        $this->repositoryMock = $this->prophesize(EntityRepository::class);
        $this->queryMock = $this->prophesize(Query::class);
        $this->qbMock = $this->prophesize(QueryBuilder::class);

        // Common entity manager setup for stdClass
        $this->registryMock->getManagerForClass('stdClass')->willReturn($this->emMock->reveal());
        $this->emMock->getRepository('stdClass')->willReturn($this->repositoryMock->reveal());
        $this->repositoryMock->createQueryBuilder('entity')->willReturn($this->qbMock->reveal());

        return [$this->registryMock, $this->emMock, $this->repositoryMock, $this->qbMock, $this->queryMock];
    }

    protected function configureQueryBuilderForChaining(ObjectProphecy $qb, Query $query, bool $hasParameters = true): void
    {
        // All QueryBuilder methods that return self for method chaining
        $qb->select(Argument::any())->willReturn($qb->reveal());
        $qb->where(Argument::any())->willReturn($qb->reveal());
        $qb->andWhere(Argument::any())->willReturn($qb->reveal());
        $qb->setParameter(Argument::any(), Argument::any(), Argument::any())->willReturn($qb->reveal());

        // Methods that return specific values
        $qb->expr()->willReturn($this->prophesize(Expr::class)->reveal());
        $qb->getQuery()->willReturn($query);

        // Parameters setup
        $parameters = $hasParameters ? new ArrayCollection(['param']) : new ArrayCollection();
        $qb->getParameters()->willReturn($parameters);
    }
}
