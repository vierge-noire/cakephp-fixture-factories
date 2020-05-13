<?php


namespace CakephpFixtureFactories\TestSuite\Truncator;


use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Hash;

class SqliteTruncator extends BaseTableTruncator
{
    public function truncate()
    {
        $tables = $this->connection->execute("
             SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence' AND name NOT LIKE '%phinxlog';
        ")->fetchAll();
        $tables = Hash::extract($tables, '{n}.0');

        $this->connection->transactional(function(ConnectionInterface $connection) use ($tables) {
            $connection->execute('pragma foreign_keys = off;');
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
            $connection->execute('pragma foreign_keys = on;');
        });
    }

    public function dropAll()
    {
        $tables = $this->connection->execute("
             SELECT name FROM sqlite_master WHERE type='table' AND name NOT IN ('sqlite_sequence');
        ")->fetchAll();
        $tables = Hash::extract($tables, '{n}.0');

        $this->connection->transactional(function(ConnectionInterface $connection) use ($tables) {
            $connection->execute('pragma foreign_keys = off;');
            foreach ($tables as $table) {
                $connection->execute("DROP TABLE IF EXISTS $table;");
            }
            $connection->execute('pragma foreign_keys = on;');
        });
    }
}