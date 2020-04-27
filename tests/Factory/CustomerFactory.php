<?php

namespace TestFixtureFactories\Test\Factory;

use Faker\Generator;
use TestFixtureFactories\Factory\BaseFactory;

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
        return $this->with('bills', BillFactory::make($parameter, $n)->without('customer'));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('address', AddressFactory::make($parameter));
    }
}
