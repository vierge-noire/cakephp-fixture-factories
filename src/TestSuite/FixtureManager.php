<?php

namespace CakephpFixtureFactories\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\FixtureManager as BaseFixtureManager;
use CakephpFixtureFactories\TestSuite\Truncator\BaseTableTruncator;
use function strpos;

/**
 * Class FixtureManager
 * @package CakephpFixtureFactories\TestSuite
 */
class FixtureManager extends BaseFixtureManager
{

    /**
     * FixtureManager constructor.
     * The config file fixture_factories is being loaded
     */
    public function __construct()
    {
        $this->loadConfig();
    }

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

    public function truncateDirtyTablesForAllTestConnections()
    {
        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            if (strpos($connectionName, 'test') === 0) {
                $this->getTruncator($connectionName)->truncate();
            }
        }
    }

    /**
     * Load the mapping between the database drivers
     * and the table truncators.
     * Add your own truncators for a driver not being covered by
     * the package in your fixture-factories.php config file
     */
    public function loadConfig()
    {
        Configure::write([
            'TestFixtureTruncators' => $this->getDefaultTruncators()
        ]);
        try {
            Configure::load('fixture_factories');
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
     * Get the driver of the given connection and
     * return the corresponding truncator
     * @param string $connectionName
     * @return BaseTableTruncator
     */
    private function getTruncator(string $connectionName): BaseTableTruncator
    {
        $connection = $this->getConnection($connectionName);
        $driver = $connection->config()['driver'];
        try {
            $truncatorName = Configure::readOrFail('TestFixtureTruncators.' . $driver);
        } catch (\RuntimeException $e) {
            throw new \PHPUnit\Framework\Exception("The DB driver $driver is not being supported");
        }
        /** @var BaseTableTruncator $truncator */
        return new $truncatorName($connection);
    }

    /**
     * Get the appropriate truncator and drop all tables
     * @param string $connectionName
     */
    public function dropTables(string $connectionName)
    {
        $this->getTruncator($connectionName)->dropAll();
    }
}
