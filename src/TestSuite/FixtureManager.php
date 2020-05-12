<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\FixtureManager as BaseFixtureManager;
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
        $this->loadConfig();

        foreach ($connections as $connectionName) {
            if (strpos($connectionName, 'test') === 0) {
                $this->truncateDirtyTables($connectionName);
            }
        }
    }

    /**
     * Load the mapping between the database drivers
     * and the table truncators.
     * Add your own truncators for a driver ot covered by
     * the package in your fixture-factories.php config file
     */
    public function loadConfig()
    {
        Configure::write([
            'TableTruncators' => $this->getDefaultTruncators()
        ]);
        try {
            Configure::load('fixture-factories');
        } catch (Exception $exception) {}
    }

    /**
     * Table truncators provided by the package
     * @return array
     */
    private function getDefaultTruncators()
    {
        return [
            \Cake\Database\Driver\Mysql::class => \CakephpFixtureFactories\TestSuite\Truncator\MySQLTruncator::class,
            \Cake\Database\Driver\Sqlite::class => \CakephpFixtureFactories\TestSuite\Truncator\SqliteTruncator::class,
        ];
    }

    /**
     * Truncate tables that are reported dirty by the database behind the given connection name
     * This is much faster than truncating all the tables for large databases
     * Currently, only an implementation supporting Mysql and MariaDB is supported
     */
    private function truncateDirtyTables(string $connectionName): void
    {
        $connection = $this->getConnection($connectionName);
        $driver = $connection->config()['driver'];
        try {
            $truncatorName = Configure::readOrFail('TableTruncators.' . $driver);
        } catch (\RuntimeException $e) {
            throw new \PHPUnit\Framework\Exception("The DB driver $driver is not being supported");
        }

        new $truncatorName($connection);
    }
}
