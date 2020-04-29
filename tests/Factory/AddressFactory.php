<?php

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class AddressFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Addresses';
    }

    protected function setDefaultTemplate(): void
    {
        $this
            ->setDefaultData(function(Generator $faker) {
                return [
                    'street' => $faker->streetAddress,
                ];
            })
            ->withCity();
    }

    public function withCity($parameter = null)
    {
        return $this->with('City', CityFactory::make($parameter));
    }
}
