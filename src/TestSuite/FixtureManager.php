<?php

namespace CakephpFixtureFactories\TestSuite;

use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\FixtureManager as BaseFixtureManager;
use CakephpFixtureFactories\TestSuite\Sniffer\MysqlTableSniffer;
use CakephpFixtureFactories\TestSuite\Sniffer\SqliteTableSniffer;
use Migrations\Migrations;
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

    /**
     * Scan all Test connections and truncate the dirty tables
     */
    public function truncateDirtyTablesForAllTestConnections()
    {
        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            if ($connectionName === 'test' || strpos($connectionName, 'test_') === 0) {
                $this->runMigration($connectionName, 'truncate');
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
            'TestFixtureTableSniffers' => $this->getDefaultTableSniffers()
        ]);
        try {
            Configure::load('fixture_factories');
        } catch (Exception $exception) {}
    }

    /**
     * Table truncators provided by the package
     * @return array
     */
    private function getDefaultTableSniffers()
    {
        return [
            \Cake\Database\Driver\Mysql::class => MysqlTableSniffer::class,
            \Cake\Database\Driver\Sqlite::class => SqliteTableSniffer::class,
        ];
    }

    /**
     * Get the appropriate truncator and drop all tables
     * @param string $connectionName
     */
    public function dropTables(string $connectionName)
    {
        $this->runMigration($connectionName, 'drop');
    }

    /**
     * Run one of the Fixture cleaning migrations: truncate or drop
     * @param string $connectionName
     * @param string $type
     */
    public function runMigration(string $connectionName, string $type)
    {
        $migration = new Migrations([
            'source' => 'Migrations' . DS . ucfirst($type),
            'connection' => $connectionName,
            'plugin' => 'CakephpFixtureFactories',
        ]);

        $migration->migrate();
        $migration->rollback(); // Rollbacking here to keep cakephp_fixture_factories_phinxlog empty
    }
}
