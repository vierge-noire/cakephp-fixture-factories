<?php

namespace TestFixtureFactories\Test\Factory;

use Faker\Generator;
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
            ->setDefaultData(function (Generator $faker) {
                return [
                    'name' => $faker->name
                ];
            })
            ->withAddress();
    }

    public function withArticles(array $parameter = null, int $n)
    {
        return $this->with('articles', ArticleFactory::make($parameter, $n)->without('authors'));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('address', AddressFactory::make($parameter));
    }
}
