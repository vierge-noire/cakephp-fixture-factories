<?php


namespace CakephpFixtureFactories\TestSuite\Truncator;


use Cake\Datasource\ConnectionInterface;

class MySQLTruncator extends BaseTableTruncator
{
    public function truncate(ConnectionInterface $connection)
    {
        $databaseName = $connection->config()['database'];
        $res = $connection->execute("
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
            $connection->execute($truncateStatement);
        }
    }
}