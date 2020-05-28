<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


use Cake\Utility\Hash;

class SqliteTableSniffer extends BaseTableSniffer
{
    public function getDirtyTables(): array
    {
        $tables = $this->getConnection()->execute("
             SELECT name FROM sqlite_sequence WHERE name NOT LIKE '%phinxlog';
        ")->fetchAll();
        return Hash::extract($tables, '{n}.0');
    }

    public function getAllTables(): array
    {
        $tables = $this->getConnection()->execute("
             SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence';
        ")->fetchAll();
        return Hash::extract($tables, '{n}.0');
    }
}