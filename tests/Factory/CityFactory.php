<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class CityFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'cities';
    }

    public function withCountry($parameter)
    {
        return $this->with('country', CountryFactory::make($parameter));
    }
}
