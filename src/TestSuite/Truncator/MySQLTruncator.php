<?php


namespace CakephpFixtureFactories\TestSuite\Truncator;


use Cake\Datasource\ConnectionInterface;

class MySQLTruncator extends BaseTableTruncator
{
    public function truncate()
    {
        $databaseName = $this->connection->config()['database'];
        $res = $this->connection->execute("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '$databaseName' and AUTO_INCREMENT > 1;
        ");
        $dirtyTables = [];
        foreach($res->fetchAll() as $tableData) {
            if ($tableData[0] !== 'phinxlog') {
                $dirtyTables[] = $tableData[0];
            }
        }
        if (count($dirtyTables)) {
            $truncateStatement = "SET FOREIGN_KEY_CHECKS=0; TRUNCATE TABLE `" . implode("`; TRUNCATE TABLE `", $dirtyTables) . "`; SET FOREIGN_KEY_CHECKS=1;";
            $this->connection->execute($truncateStatement);
        }
    }

    public function dropAll()
    {
        $databaseName = $this->connection->config()['database'];
        $res = $this->connection->execute("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '$databaseName';
        ");
        $tables = [];
        foreach($res->fetchAll() as $tableData) {
                $tables[] = $tableData[0];
        }
        if (count($tables)) {
            $truncateStatement = "SET FOREIGN_KEY_CHECKS=0; DROP TABLE `" . implode("`; DROP TABLE `", $tables) . "`; SET FOREIGN_KEY_CHECKS=1;";
            $this->connection->execute($truncateStatement);
        }
    }
}