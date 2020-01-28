<?php

namespace TestFixtureFactories\TestSuite;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\Fixture\FixtureManager as BaseFixtureManager;
use function array_filter;
use function array_map;
use function count;
use function debug;
use function implode;
use function microtime;
use function strpos;

/**
 * Class FixtureManager
 * @package TestFixtureFactories\TestSuite
 */
class FixtureManager extends BaseFixtureManager
{
    /**
     * @param string $name
     * @return mixed
     */
    public function getConnection($name = 'test')
    {
        return ConnectionManager::get($name);
    }

    /**
     *
     */
    public function startTest()
    {
        $this->_initDb();

        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            if (strpos($connectionName, 'test') === 0) {
                $this->truncateDirtyTables($connectionName);
            }
        }
    }

    /**
     * @param string $connectionName
     */
    private function truncateDirtyTablesUsingCount(string $connectionName)
    {
        // not fully implemented as truncateDirtyTables seem sufficient
        $connection = $this->getConnection($connectionName);
        $databaseName = $connection->config()['database'];
        $tables = $connection->getSchemaCollection()->listTables();
        $tables = array_filter($tables, function ($table) {
            if (strpos($table, 'phinxlog') !== false) {
                return false;
            }
            return true;
        });
        $truncateStatement = null;
        foreach ($tables as $table) {
            $truncateStatement .= "SELECT '$table' AS table_name, COUNT(*) AS count FROM $databaseName.$table HAVING count > 0 UNION ";
        }
        $res = $connection->execute($truncateStatement);
        debug($res->fetchAll());
    }

    /**
     * Truncate tables that are reported dirty by the database behind the given connection name
     * This is much faster than truncating all the tables for large databases
     * Currently, only an implementation supporting Mysql and MariaDB is supported
     */
    private function truncateDirtyTables(string $connectionName)
    {
        $connection = $this->getConnection($connectionName);
        $databaseName = $connection->config()['database'];
        $res = $connection->execute("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '$databaseName' and table_rows > 0;
        ");
        $dirtyTables = array_map(function ($tableData) {
            return $tableData[0];
        }, $res->fetchAll());
        if (count($dirtyTables)) {
            $truncateStatement = "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE `" . implode("`; TRUNCATE TABLE `", $dirtyTables) . "`; SET FOREIGN_KEY_CHECKS=1;";
            $connection->execute($truncateStatement);
        }
    }

    /**
     * Truncate all tables for the given connection name
     */
    private function truncateAllTables(string $connectionName)
    {
        $tables = $this->getConnection($connectionName)->getSchemaCollection()->listTables();

        foreach ($tables as $i => $table) {
            if (strpos($table, 'phinxlog') !== false) {
                unset($tables[$i]);
            }
        }

        if (!empty($tables)) {
            $start = microtime(true);
            $this->getConnection($connectionName)->execute(
                "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE `" . implode("`; TRUNCATE TABLE `",
                    $tables) . "`; SET FOREIGN_KEY_CHECKS=1;"
            );
            debug("SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE `" . implode("`; TRUNCATE TABLE `",
                    $tables) . "`; SET FOREIGN_KEY_CHECKS=1;");
            $time_elapsed_secs = microtime(true) - $start;
            debug("elapsed time : $time_elapsed_secs");
        }
    }
}
