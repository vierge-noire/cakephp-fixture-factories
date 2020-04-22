<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class AddressFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "addresses";
    }

    public function withCountry($parameter)
    {
        return $this->with('country', ArticleFactory::make($parameter));
    }
}
