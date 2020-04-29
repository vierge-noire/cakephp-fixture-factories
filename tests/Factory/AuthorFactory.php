<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

class AuthorFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return "Authors";
    }

    protected function setDefaultTemplate()
    {
        $this
            ->setDefaultData(function (Generator $faker) {
                return [
                    'name' => $faker->name
                ];
            })
            ->withAddress();
    }

    public function withArticles($parameter = null, int $n = 1)
    {
        return $this->with('Articles', ArticleFactory::make($parameter, $n)->without('authors'));
    }

    public function withAddress($parameter = null)
    {
        return $this->with('Address', AddressFactory::make($parameter));
    }
}
