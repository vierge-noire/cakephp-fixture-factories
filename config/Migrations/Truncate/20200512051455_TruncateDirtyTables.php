<?php


use Migrations\AbstractMigration;
use CakephpFixtureFactories\TestSuite\Sniffer\PostgresTableSniffer;

class TruncateDirtyTables extends AbstractMigration
{
    use \CakephpFixtureFactories\TestSuite\Sniffer\TableSnifferFinder;

    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        if ($this->getTableSniffer() instanceof PostgresTableSniffer) {
            $this->postgresTruncate();
        } else {
            foreach ($this->getTableSniffer()->getDirtyTables() as $table) {
                $this->table($table)->truncate();
            }
        }
    }

    public function down()
    {}

    /**
     * The actual Phinx truncation does no restart sequences
     * This needs to be done in order to accurately detect dirty tables
     */
    private function postgresTruncate()
    {
        $tables = implode(', ', $this->getTableSniffer()->getDirtyTables());

        if (!empty($tables)) {
            $this->getTableSniffer()->getConnection()->execute("
                TRUNCATE $tables RESTART IDENTITY;
            ");
        }
    }
}
