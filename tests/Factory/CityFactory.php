<?php

namespace TestFixtureFactories\Test\Factory;

use Faker\Generator;
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
            ->setDefaultData(function(Generator $faker) {
                return [
                    'name' => $faker->city,
                ];
            })
            ->withCountry();
    }

    public function withCountry($parameter = null)
    {
        return $this->with('country', CountryFactory::make($parameter));
    }
}
