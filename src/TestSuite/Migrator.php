<?php


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
        $migrator->setConfig($config);

        foreach ($migrator->getConfig() as $config) {
            $migrations = new Migrations($config);
            if ($migrator->isMigrationMissing($migrations)) {
                $migrator->_fixtureManager->dropTables($config['connection']);
            }
            $migrations->migrate();
        }
    }

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
    public function setConfig(array $config = []): void
    {
        $config = array_merge(Configure::read('TestFixtureMigrations', []), $config);
        if (empty($config)) {
            $config = [['connection' => 'test', 'source' => 'Migrations']];
        }
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }
}