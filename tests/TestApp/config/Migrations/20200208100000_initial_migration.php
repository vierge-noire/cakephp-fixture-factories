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
            ->addTimestamps()
            ->create();

        $this->table('articles')
            ->addPrimaryKey(['id'])
            ->addColumn('title', 'string', [
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('content', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addTimestamps()
            ->create();

        $this->table('articles_authors')
            ->addColumn('author_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_id', 'integer', [
                'default' => null,
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
                'default' => null,
                'limit' => 128,
                'null' => false,
            ])
            ->addColumn('author_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => true,
            ])
            ->addColumn('city_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();

        $this->table('cities')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addColumn('country_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();

        $this->table('countries')
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addTimestamps()
            ->create();

        $this->table('customers')
            ->addPrimaryKey(['id'])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 128,
                'null' => true,
            ])
            ->addTimestamps()
            ->create();

        $this->table('bills')
            ->addPrimaryKey(['id'])
            ->addColumn('customer_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('article_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('amount', 'float', [
                'default' => 0,
                'precision' => 10,
                'scale' => 2,
                'null' => false,
            ])
            ->addTimestamps()
            ->create();
    }
}
