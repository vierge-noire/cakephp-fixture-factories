<?php
use Migrations\AbstractMigration;

class CreateBills extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        $this->table('bills')
            ->addPrimaryKey(['id'])
            ->addColumn('customer_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('amount', 'float', [
                'scale' => 2,
                'null' => false,
            ])
            ->addIndex('customer_id')
            ->addIndex('article_id')
            ->addTimestamps()
            ->create();
    }

    public function down()
    {
        $this->table('bills')->drop();
    }
}
