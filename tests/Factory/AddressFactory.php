<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

class AddressFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Addresses';
    }

    protected function setDefaultTemplate()
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
