<?php

namespace CakephpFixtureFactories\Test\Factory;

use Faker\Generator;

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
