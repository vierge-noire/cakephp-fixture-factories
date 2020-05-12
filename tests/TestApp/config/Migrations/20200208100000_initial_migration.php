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
            ->addColumn('biography', 'text', [
                'null' => true,
                'default' => 'empty',
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

        $this->table('customers')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();

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
}
