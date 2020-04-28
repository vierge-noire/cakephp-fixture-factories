<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;
use CakephpFixtureFactories\Factory\BaseFactory;

class ArticleWithFiveBillsFactory extends ArticleFactory
{
    protected function setDefaultTemplate()
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
