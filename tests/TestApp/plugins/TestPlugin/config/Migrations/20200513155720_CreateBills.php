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
    public function change()
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
                'precision' => 10,
                'scale' => 2,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();
    }

    public function down()
    {
        $this->table('bills')->drop();
    }
}
