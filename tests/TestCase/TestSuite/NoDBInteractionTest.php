<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\TestCase\TestSuite;


use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Test\Factory\CityFactory;
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