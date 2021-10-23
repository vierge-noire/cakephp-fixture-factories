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

class TableWithoutModelMigration extends AbstractMigration
{
    public function up()
    {
        $this->table('table_without_model')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('foreign_key', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('binding_key', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('country_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addTimestamps('created', 'modified')
            ->save();
    }

    public function down()
    {
        $this->table('table_without_model')->drop();
    }
}
