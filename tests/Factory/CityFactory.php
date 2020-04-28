<?php

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class CityFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Cities';
    }

    protected function setDefaultTemplate(): void
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
