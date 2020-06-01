<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;


use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\TestSuite\SkipTablesTruncation;
use TestApp\Model\Entity\Country;

class NoDBInteractionTest extends \Cake\TestSuite\TestCase
{
    use SkipTablesTruncation;

    public function testSkipTablesTruncation()
    {
        $this->assertSame(true, $this->skipTablesTruncation);
    }

    public function testCreateCountry()
    {
        $this->assertInstanceOf(Country::class, CountryFactory::make()->persist());
    }

    public function testFindCountry()
    {
        $countries = TableRegistry::getTableLocator()->get('countries')->find();
        $this->assertGreaterThan(0, $countries->count());
    }
}