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

class InitialMigration extends AbstractMigration
{
    public $autoId = false;

    public function up()
    {
        $this->table('authors')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'generated' => \Phinx\Db\Adapter\PostgresAdapter::GENERATED_BY_DEFAULT,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('address_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('business_address_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('biography', 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('field_with_setter_1', 'string', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('field_with_setter_2', 'string', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('field_with_setter_3', 'string', [
                'default' => null,
                'null' => true,
            ])
            ->addIndex('address_id')
            ->addIndex('business_address_id')
            ->addTimestamps('created', 'modified')
            ->create();

        $this->table('articles')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'generated' => \Phinx\Db\Adapter\PostgresAdapter::GENERATED_BY_DEFAULT,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('title', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('body', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn(\TestApp\Model\Entity\Article::HIDDEN_PARAGRAPH_PROPERTY_NAME, 'text', [
                'default' => null,
                'null' => true,
            ])
            ->addColumn('published', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addTimestamps('created', 'modified')
            ->create();

        $this->table('articles_authors')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'generated' => \Phinx\Db\Adapter\PostgresAdapter::GENERATED_BY_DEFAULT,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('author_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex([
                'author_id',
            ])
            ->addIndex([
                'article_id',
            ])
            ->create();

        $this->table('addresses')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'generated' => \Phinx\Db\Adapter\PostgresAdapter::GENERATED_BY_DEFAULT,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('street', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('city_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('city_id')
            ->addTimestamps('created', 'modified')
            ->create();

        $this->table('cities')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'generated' => \Phinx\Db\Adapter\PostgresAdapter::GENERATED_BY_DEFAULT,
            ])
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
            // These fields should not be set by the DB by default
            // They are used to test that the TimeStampBehavior is
            // correctly applied by default.
            ->addColumn('created', 'timestamp', ['null' => true])
            ->addColumn('modified', 'timestamp', ['null' => true])
            ->create();

        $this->table('countries')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'limit' => 11,
                'generated' => \Phinx\Db\Adapter\PostgresAdapter::GENERATED_BY_DEFAULT,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('unique_stamp', 'string', [
                'limit' => 128,
                'null' => true,
            ])
            // These fields should not be set by the DB by default
            // They are used to test that the TimeStampBehavior is
            // correctly applied by default.
            ->addColumn('created', 'timestamp', ['null' => true])
            ->addColumn('modified', 'timestamp', ['null' => true])
            ->create();

        $this->table('authors')
            ->addForeignKey('address_id', 'addresses', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->save();

        $this->table('cities')
            ->addForeignKey('country_id', 'countries', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->save();

        $this->table('countries')
            ->addIndex('unique_stamp', ['unique' => true])
            ->save();
    }

    public function down()
    {
        $this->table('articles_authors')->drop();
        $this->table('articles')->drop();
        $this->table('authors')->drop();
        $this->table('addresses')->drop();
        $this->table('cities')->drop();
        $this->table('countries')->drop();
    }
}
