<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Truncator;


use Cake\Datasource\ConnectionInterface;

abstract class BaseTableTruncator
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    abstract public function truncate();

    abstract public function dropAll();

    /**
     * BaseTableTruncator constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }
}