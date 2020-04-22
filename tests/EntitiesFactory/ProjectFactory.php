<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class ProjectFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'projects';
    }

    public function withAddress($parameter): ProjectFactory
    {
        return $this->with('address', AuthorFactory::make($parameter));
    }
}
