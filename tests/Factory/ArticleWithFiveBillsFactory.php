<?php

namespace TestFixtureFactories\Test\Factory;

use Faker\Generator;
use TestFixtureFactories\Factory\BaseFactory;

class ArticleWithFiveBillsFactory extends ArticleFactory
{
    protected function setDefaultTemplate(): void
    {
        $this
            ->setDefaultData(function (Generator $faker) {
                return [
                    'title' => 'Article with 5 bills',
                ];
            })
            ->withBills(null, 5);

        parent::setDefaultTemplate();
    }
}
