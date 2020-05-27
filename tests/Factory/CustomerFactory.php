<?php

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class CustomerFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Customers';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'name' => $faker->lastName,
            ];
        });
    }

    public function withBills($parameter = null, $n = 1)
    {
        return $this->with('Bills', BillFactory::make($parameter, $n)->without('Customer'));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('Address', AddressFactory::make($parameter));
    }
}
