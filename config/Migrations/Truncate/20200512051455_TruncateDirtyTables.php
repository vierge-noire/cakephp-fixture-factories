<?php
use Migrations\AbstractMigration;

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
        foreach ($this->getTableSniffer()->getDirtyTables() as $table) {
            $this->table($table)
                ->truncate();
        }
    }

    public function down()
    {}
}
