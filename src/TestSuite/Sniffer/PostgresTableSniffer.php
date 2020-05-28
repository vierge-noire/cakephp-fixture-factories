<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Utility\Hash;

class PostgresTableSniffer extends BaseTableSniffer
{
    public function getDirtyTables(): array
    {
        $tables = $this->getConnection()->execute("
            SELECT substr(sequencename, 1, length(sequencename) - 7)
            FROM pg_sequences
            WHERE last_value > 0;
        ")
            ->fetchAll();
        return Hash::extract($tables, '{n}.0');
    }

    public function getAllTables(): array
    {
        $tables = $this->getConnection()->execute("            
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = 'public'            
        ")->fetchAll();
        return Hash::extract($tables, '{n}.0');
    }
}