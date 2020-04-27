<?php

namespace TestFixtureFactories\Test\Factory;

use Faker\Generator;
use TestFixtureFactories\Factory\BaseFactory;

class CountryFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'countries';
    }

    protected function setDefaultTemplate()
    {
        return $this
            ->setDefaultData(function(Generator $faker) {
                return [
                    'name' => $faker->country,
                ];
            });
    }
}
