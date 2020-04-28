<?php

namespace CakephpFixtureFactories\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

class AuthorFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "Authors";
    }

    protected function setDefaultTemplate(): void
    {
        $this
            ->setDefaultData(function (Generator $faker) {
                return [
                    'name' => $faker->name
                ];
            })
            ->withAddress();
    }

    public function withArticles(array $parameter = null, int $n)
    {
        return $this->with('Articles', ArticleFactory::make($parameter, $n)->without('authors'));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('Address', AddressFactory::make($parameter));
    }
}
