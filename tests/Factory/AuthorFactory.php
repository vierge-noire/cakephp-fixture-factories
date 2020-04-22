<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class AuthorFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "authors";
    }

    public function withArticle($parameter)
    {
        return $this->with('article', ArticleFactory::make($parameter));
    }

    public function withAddress($parameter)
    {
        return $this->with('address', AddressFactory::make($parameter));
    }
}
