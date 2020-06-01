<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Database\Exception;
use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Hash;

abstract class BaseTableSniffer
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * Find all tables where an insert happened
     * This also includes empty tables, where a delete
     * was performed after an insert
     * @return array
     */
    abstract public function getDirtyTables(): array;

    /**
     * List all tables
     * @return array
     */
    abstract public function getAllTables(): array;

    /**
     * BaseTableTruncator constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * In case where the query fails because the database queried does
     * not exist, an exception is thrown.
     * @param string $query
     * @return array
     */
    protected function executeQuery(string $query): array
    {
        try {
            $tables = $this->getConnection()->execute($query)->fetchAll();
        } catch (\Exception $e) {
            $name = $this->getConnection()->configName();
            $db = $this->getConnection()->config()['database'];
            throw new Exception("Error in the connection '$name'. Is the database '$db' created and accessible?");
        }

        return Hash::extract($tables, '{n}.0');
    }
}