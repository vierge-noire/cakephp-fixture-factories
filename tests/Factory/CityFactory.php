<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class CityFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'cities';
    }

    protected function setDefaultTemplate()
    {
        return $this
            ->patchData([
                'name' => $this->getFaker()->city,
            ])
            ->withCountry();
    }

    public function withCountry($parameter = null)
    {
        return $this->with('country', CountryFactory::make($parameter));
    }
}
