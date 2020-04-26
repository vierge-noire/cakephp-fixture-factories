<?php

namespace TestFixtureFactories\Test\Factory;

use TestFixtureFactories\Factory\BaseFactory;

class ArticleWithFiveBillsFactory extends ArticleFactory
{
    protected function setDefaultTemplate()
    {
        $this
            ->patchData([
                'title' => 'Article with 5 bills',
            ])
            ->withBills(null, 5);

        return parent::setDefaultTemplate();
    }
}
