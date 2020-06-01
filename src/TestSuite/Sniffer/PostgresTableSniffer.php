<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\TestSuite\Sniffer;


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
}