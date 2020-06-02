<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Database\Connection;

class SqliteTableSniffer extends BaseTableSniffer
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getDirtyTables(): array
    {
//        $res = $this->executeQuery("
//            PRAGMA foreign_keys;
//        ");
//        dd($res);

        $this->executeQuery("
            PRAGMA foreign_keys = ON;
        ");

        return $this->executeQuery("
             SELECT name FROM sqlite_sequence WHERE name NOT LIKE '%phinxlog';
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
                foreach ($tables as $table) {
                    $connection
                        ->newQuery()
                        ->delete($table)
                        ->execute();
                    $connection
                        ->newQuery()
                        ->delete('sqlite_sequence')
                        ->where(['name' => $table])
                        ->execute();
                }
            });
        });
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getAllTables(): array
    {
        return $this->executeQuery("
             SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence';
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
                foreach ($tables as $table) {
                    $connection->execute("DROP TABLE IF EXISTS $table;");
                }
            });
        });
    }
}