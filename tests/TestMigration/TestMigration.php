<?php

namespace CakephpFixtureFactories\Test\TestMigration;

use Migrations\AbstractMigration;

class TestMigration extends AbstractMigration
{
    use \CakephpFixtureFactories\TestSuite\Sniffer\TableSnifferFinder;
}
