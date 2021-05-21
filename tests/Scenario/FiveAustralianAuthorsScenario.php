<?php


namespace CakephpFixtureFactories\Test\Scenario;


use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;

class FiveAustralianAuthorsScenario implements FixtureScenarioInterface
{
    const COUNTRY_NAME = 'Australia';

    const N = 5;

    public function load()
    {
        AuthorFactory::make(self::N)->fromCountry(self::COUNTRY_NAME)->persist();
    }
}
