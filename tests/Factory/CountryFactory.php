<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

class CountryFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Countries';
    }

    protected function setDefaultTemplate()
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'name' => $faker->country,
            ];
        });
    }
}
