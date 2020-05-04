<?php


namespace CakephpFixtureFactories\TestSuite;


use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionInterface;

abstract class TableTruncator
{
    abstract public function truncate(ConnectionInterface $connection);

    public function __construct(ConnectionInterface $connection)
    {
        $this->truncate($connection);
    }
}