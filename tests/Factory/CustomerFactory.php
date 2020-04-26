<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class CustomerFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Customers';
    }

    protected function setDefaultTemplate()
    {
        return $this
            ->patchData([
                'name' => $this->getFaker()->lastName
            ]);
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
