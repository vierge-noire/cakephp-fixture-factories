<?php


namespace TestFixtureFactories\TestSuite;

use Cake\Datasource\ConnectionManager;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\Fixture\FixtureManager as BaseFixtureManager;

class FixtureManager extends BaseFixtureManager
{
    
    public function getConnection($name = 'test')
    {
        return ConnectionManager::get($name);
    }

    public function startTest()
    {
        $this->_initDb();

        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            if (strpos($connectionName, 'test') === 0) {
                $this->_truncateTables($connectionName);
            }
        }
    }

    /**
     * Delete all table content
     */
    private function _truncateTables(string $connectionName)
    {
        $tables = $this->getConnection($connectionName)->getSchemaCollection()->listTables();

        foreach ($tables as $i => $table) {
            if (strpos($table, 'phinxlog') !== false) {
                unset($tables[$i]);
            }
        }

        if (!empty($tables)) {
            $this->getConnection($connectionName)->execute(
                "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE `" . implode("`; TRUNCATE TABLE `", $tables) . "`; SET FOREIGN_KEY_CHECKS=1;"
            );
        }
    }
}