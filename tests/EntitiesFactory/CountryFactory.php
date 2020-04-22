<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class CountryFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "countries";
    }

    public function withContinent($parameter)
    {
        $this->with('continent', OptionFactory::make($parameter));
    }
}
