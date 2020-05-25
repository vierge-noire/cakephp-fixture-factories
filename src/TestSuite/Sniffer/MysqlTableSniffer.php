<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Utility\Hash;

class MysqlTableSniffer extends BaseTableSniffer
{
    public function getDirtyTables(): array
    {
        $databaseName = $this->connection->config()['database'];
        $tables = $this->connection->execute("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE
                TABLE_SCHEMA = '$databaseName'
                AND table_name NOT LIKE '%phinxlog'
                AND AUTO_INCREMENT > 1;
        ")->fetchAll();
        return Hash::extract($tables, '{n}.0');

    }

    public function getAllTables(): array
    {
        $databaseName = $this->connection->config()['database'];
        $tables = $this->connection->execute("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '$databaseName';
        ")->fetchAll();
        return Hash::extract($tables, '{n}.0');
    }
}