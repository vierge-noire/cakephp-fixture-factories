<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


class MysqlTableSniffer extends BaseTableSniffer
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getDirtyTables(): array
    {
        $databaseName = $this->getConnection()->config()['database'];

        return $this->executeQuery("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE
                TABLE_SCHEMA = '$databaseName'
                AND table_name NOT LIKE '%phinxlog'
                AND AUTO_INCREMENT > 1;
        ");
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getAllTables(): array
    {
        $databaseName = $this->getConnection()->config()['database'];

        return $this->executeQuery("
            SELECT table_name, table_rows
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = '$databaseName';
        ");
    }
}