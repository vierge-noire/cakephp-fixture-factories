<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class AddressFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'addresses';
    }

    protected function setDefaultTemplate()
    {
        return $this->patchData([
            'street' => $this->getFaker()->streetAddress,
        ])->withCity();
    }

    public function withCity($parameter = null)
    {
        return $this->with('City', CityFactory::make($parameter));
    }
}
