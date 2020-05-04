<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite;

use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\FixtureManager as BaseFixtureManager;
use Cake\Utility\Hash;
use function count;
use function implode;
use function strpos;

/**
 * Class FixtureManager
 * @package CakephpFixtureFactories\TestSuite
 */
class FixtureManager extends BaseFixtureManager
{
    /**
     * @param string $name
     * @return ConnectionInterface
     */
    public function getConnection($name = 'test')
    {
        return ConnectionManager::get($name);
    }

    public function initDb()
    {
        $this->_initDb();
    }

    public function truncateDirtyTablesForAllConnections()
    {
        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            if (strpos($connectionName, 'test') === 0) {
                $this->truncateDirtyTables($connectionName);
            }
        }
    }

    /**
     * Truncate tables that are reported dirty by the database behind the given connection name
     * This is much faster than truncating all the tables for large databases
     * Currently, only an implementation supporting Mysql and MariaDB is supported
     */
    private function truncateDirtyTables(string $connectionName)
    {
        $connection = $this->getConnection($connectionName);
        $driver = $connection->config()['driver'];
        switch ($driver) {
            case Mysql::class:
                new MySQLTruncator($connection);
                break;
            case Sqlite::class:
                new SqliteTruncator($connection);
                break;
            default:
                throw new \PHPUnit\Framework\Exception("The DB driver $driver is not being supported");
                break;
        }
    }
}
