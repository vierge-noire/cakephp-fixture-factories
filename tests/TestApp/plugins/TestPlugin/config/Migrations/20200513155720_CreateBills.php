<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
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
            ->addTimestamps('created', 'modified')
            ->create();
    }

    public function down()
    {
        $this->table('bills')->drop();
    }
}
