<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class BillFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Bills';
    }

    public function withArticle($parameter)
    {
        return $this->with('article', ArticleFactory::make($parameter));
    }

    public function withCustomer($parameter)
    {
        return $this->with('customer', CustomerFactory::make($parameter));
    }
}
