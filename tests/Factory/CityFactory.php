<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

class CityFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Cities';
    }

    protected function setDefaultTemplate()
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'name' => $faker->city,
            ];
        })
        ->withCountry();
    }

    public function withCountry($parameter = null)
    {
        return $this->with('Country', CountryFactory::make($parameter));
    }
}
