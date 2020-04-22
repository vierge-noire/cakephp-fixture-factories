<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class OptionFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "options";
    }
}
