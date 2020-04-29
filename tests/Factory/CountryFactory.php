<?php

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class CountryFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Countries';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'name' => $faker->country,
            ];
        });
    }
}
