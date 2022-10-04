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
namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use TestDatabaseCleaner\TruncateDirtyTablesTrait;

class BaseFactoryTimestampBehaviorTest extends TestCase
{
    use TruncateDirtyTablesTrait;

    /**
     * The countries and cities tables do not have default timestamp.
     * We test here that the TimestampBehavior is well activated.
     */
    public function testBaseFactoryTimeStampBehavior()
    {
        $city = CityFactory::make()->withCountry()->getEntity();
        $this->assertNull($city->created);
        $this->assertNull($city->modified);
        $this->assertNull($city->country->created);
        $this->assertNull($city->country->modified);

        $country = CountryFactory::make()->persist();
        $this->assertTrue($country->created->isToday());
        $this->assertTrue($country->modified->isToday());

        $city = CityFactory::make()->persist();
        $this->assertTrue($city->created->isToday());
        $this->assertTrue($city->modified->isToday());
        $this->assertTrue($city->country->created->isToday());
        $this->assertTrue($city->country->modified->isToday());
    }
}
