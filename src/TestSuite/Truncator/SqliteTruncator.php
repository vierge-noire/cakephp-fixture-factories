<?php


namespace CakephpFixtureFactories\TestSuite\Truncator;


use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Hash;

class SqliteTruncator extends BaseTableTruncator
{
    public function truncate(ConnectionInterface $connection)
    {
        $tables = $connection->execute("
             SELECT name FROM sqlite_master WHERE type='table' AND name NOT IN ('sqlite_sequence', 'phinxlog');
        ")->fetchAll();
        $tables = Hash::extract($tables, '{n}.0');

        $connection->transactional(function(ConnectionInterface $connection) use ($tables) {
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
}