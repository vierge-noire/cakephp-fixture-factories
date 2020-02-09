<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class EntityFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Entities';
    }
}
