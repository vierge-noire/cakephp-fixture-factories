<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Truncator;


use Cake\Datasource\ConnectionInterface;

abstract class BaseTableTruncator
{
    abstract public function truncate(ConnectionInterface $connection);

    public function __construct(ConnectionInterface $connection)
    {
        $this->truncate($connection);
    }
}