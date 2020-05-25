<?php
use Migrations\AbstractMigration;

class DropAllTables extends AbstractMigration
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
        foreach ($this->getTableSniffer()->getAllTables() as $table) {
            if ($table !== "cakephp_fixture_factories_phinxlog") {
                $this->table($table)->drop()->save();
            }
        }
    }

    public function down()
    {}
}
