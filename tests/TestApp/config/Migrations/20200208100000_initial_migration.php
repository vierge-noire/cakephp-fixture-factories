<?php

use Migrations\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up()
    {
        $this->table('authors')
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
            ->addIndex('address_id')
            ->addIndex('business_address_id')
            ->addTimestamps()
            ->create();

        $this->table('articles')
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
            ->addColumn('published', 'integer', [
                'default' => 0,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();

        $this->table('articles_authors')
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
            ->addTimestamps()
            ->create();

        $this->table('cities')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('country_id', 'integer', [
                'limit' => 11,
                'null' => false,
            ])
            ->addIndex('country_id')
            ->addTimestamps()
            ->create();

        $this->table('countries')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();

        $this->table('authors')
            ->addForeignKey('address_id', 'addresses', 'id', ['delete'=>'RESTRICT', 'update'=>'CASCADE'])
            ->save();

        $this->table('cities')
            ->addForeignKey('country_id', 'countries', 'id', ['delete'=>'RESTRICT', 'update'=>'CASCADE'])
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
