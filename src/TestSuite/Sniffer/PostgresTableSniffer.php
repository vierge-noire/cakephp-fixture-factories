<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Database\Connection;

class PostgresTableSniffer extends BaseTableSniffer
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getDirtyTables(): array
    {
        return $this->executeQuery("
            SELECT substr(sequencename, 1, length(sequencename) - 7)
            FROM pg_sequences
            WHERE last_value > 0;
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
                    'TRUNCATE "' . implode('", "', $tables) . '" RESTART IDENTITY CASCADE;'
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
        return $this->executeQuery("            
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'            
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
                    $connection->execute(
                        'DROP TABLE IF EXISTS "' . $table  . '" CASCADE;'
                    );
                }
            });
        });
    }
}