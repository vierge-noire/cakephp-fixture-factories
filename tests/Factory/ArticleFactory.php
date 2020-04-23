<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class ArticleFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "articles";
    }

    public function withAuthors($parameter, int $n = 1)
    {
        return $this->with('authors', AuthorFactory::make($parameter, $n));
    }

    public function withBills($parameter, int $n = 1)
    {
        return $this->with('bills', BillFactory::make($parameter, $n));
    }
}
