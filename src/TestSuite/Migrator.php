<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite;


use Cake\Core\Configure;
use Migrations\Migrations;

class Migrator
{
    private $config;

    /**
     * @var FixtureManager
     */
    public $_fixtureManager;

    public function __construct()
    {
        $this->_fixtureManager = new FixtureManager();
    }

    public static function migrate(array $config = [])
    {
        $migrator = new static();

        $migrator
            ->setConfig($config)
            ->dropTablesForMissingMigrations()
            ->runAllMigrations();
    }

    /**
     * Run migrations for all configured migrations
     */
    private function runAllMigrations()
    {
        foreach ($this->getConfig() as $config) {
            $migrations = new Migrations($config);
            $migrations->migrate($config);
        }
    }

    /**
     * If a migration is missing, all tables of the considered connection are droped
     * @return $this
     */
    private function dropTablesForMissingMigrations()
    {
        foreach ($this->getConfig() as $config) {
            $migrations = new Migrations($config);
            if ($this->isMigrationMissing($migrations)) {
                $this->_fixtureManager->dropTables($config['connection'] ?? 'test');
            }
        }
        return $this;
    }

    /**
     * Checks if any migrations are up but missing
     * @param Migrations $migrations
     * @return bool
     */
    public function isMigrationMissing(Migrations $migrations)
    {
        $status = $migrations->status();
        foreach ($status as $migration) {
            if ($migration['status'] === 'up' && ($migration['missing'] ?? false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param mixed $config
     */
    public function setConfig(array $config = [])
    {
        $config = array_merge(Configure::read('TestFixtureMigrations', []), $config);

        if (empty($config)) {
            $config = [['connection' => 'test', 'source' => 'Migrations']];
        }

        if (!isset($config[0])) {
            $config = [$config];
        }

        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }
}