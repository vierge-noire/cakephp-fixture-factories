<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class CountryFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'countries';
    }
}
