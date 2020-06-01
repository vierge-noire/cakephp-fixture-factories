<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


class SqliteTableSniffer extends BaseTableSniffer
{
    /**
     * @inheritDoc
     * @return array
     */
    public function getDirtyTables(): array
    {
        return $this->executeQuery("
             SELECT name FROM sqlite_sequence WHERE name NOT LIKE '%phinxlog';
         ");
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
}