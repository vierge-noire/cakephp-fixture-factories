<?php

declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Migrations\AbstractMigration;

class RoomsCatsDogsTablesMigration extends AbstractMigration
{
    public function up()
    {
        $this->table('rooms')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('virtual_unique_stamp', 'string', [
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('address_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('address_id')
            ->addColumn('cat_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('cat_id')
            ->addColumn('dog_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('dog_id')
            ->create();

        $this->table('cats')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('virtual_unique_stamp', 'string', [
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('country_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('country_id')
            ->create();

        $this->table('dogs')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('virtual_unique_stamp', 'string', [
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('country_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('country_id')
            ->create();
    }

    public function down()
    {
        $this->table('rooms_table')->drop();
        $this->table('cats_table')->drop();
        $this->table('dogs_table')->drop();
    }
}
