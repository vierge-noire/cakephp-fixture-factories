<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class EntityFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'Entities';
    }

    public function withAddress($parameter)
    {
        return $this->with('address', AuthorFactory::make($parameter));
    }

    public function withProjects($parameter, $times)
    {
        return $this->with('project', ProjectFactory::make($parameter, $times));
    }
}
