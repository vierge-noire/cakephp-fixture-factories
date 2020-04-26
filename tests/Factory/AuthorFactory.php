<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class AuthorFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "authors";
    }

    protected function setDefaultTemplate()
    {
        return $this
            ->patchData([
                'name' => $this->getFaker()->lastName
            ])
            ->withAddress();
    }

    public function withArticles(array $parameter, int $n)
    {
        return $this->with('articles', ArticleFactory::make($parameter, $n));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('address', AddressFactory::make($parameter));
    }
}
