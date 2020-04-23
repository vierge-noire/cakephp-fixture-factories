<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class CustomerFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Customers';
    }

    public function withBills($parameter, $n = 1)
    {
        return $this->with('bills', BillFactory::make($parameter, $n));
    }
}
