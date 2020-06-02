<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Database\Connection;

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
     * @return bool|void
     * @throws \Exception
     */
    public function truncateDirtyTables()
    {
        $tables = $this->getDirtyTables();
        if (empty($tables)) {
            return;
        }
        $this->getConnection()->disableConstraints(function (Connection $connection) use ($tables) {
            $connection->transactional(function(Connection $connection) use ($tables) {
                $connection->execute(
                    "TRUNCATE TABLE `" . implode("`; TRUNCATE TABLE `", $tables) . "`;"
                );
            });
        });
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

    /**
     * @inheritDoc
     * @return array|void
     * @throws \Exception
     */
    public function dropAllTables()
    {
        $tables = $this->getAllTables();

        $this->getConnection()->disableConstraints(function (Connection $connection) use ($tables) {
            $connection->transactional(function(Connection $connection) use ($tables) {
                $connection->execute(
                    "DROP TABLE IF EXISTS `" . implode("`; DROP TABLE IF EXISTS `", $tables) . "`;"
                );
            });
        });
    }
}