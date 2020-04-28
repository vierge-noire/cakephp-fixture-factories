<?php

namespace TestFixtureFactories\Test\Factory;

use Faker\Generator;
use TestFixtureFactories\Factory\BaseFactory;

class BillFactory extends BaseFactory
{
    protected function getRootTableRegistryName(): string
    {
        return 'TestPlugin.Bills';
    }

    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function(Generator $faker) {
            return [
                'amount' => $faker->numberBetween(0, 1000),
            ];
        })
        ->withArticle()
        ->withCustomer();
    }

    public function withArticle($parameter = null)
    {
        return $this->with('Article', ArticleFactory::make($parameter));
    }

    public function withCustomer($parameter = null)
    {
        return $this->with('Customer', CustomerFactory::make($parameter));
    }
}
