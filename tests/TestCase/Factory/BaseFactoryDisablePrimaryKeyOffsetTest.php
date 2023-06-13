<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.8.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use TestApp\Model\Entity\Country;

class BaseFactoryDisablePrimaryKeyOffsetTest extends TestCase
{
    public static function dataForTestDisablePrimaryKeyOffset()
    {
        return [
            [rand(1, 1000000)],
            [rand(1, 1000000)],
            [rand(1, 1000000)],
        ];
    }

    /**
     * @dataProvider dataForTestDisablePrimaryKeyOffset
     * @param int $cityOffset
     */
    public function testDisablePrimaryKeyOffset(int $cityOffset)
    {
        $n = 10;
        $cities = CityFactory::make($n)
            ->setPrimaryKeyOffset($cityOffset)
            ->disablePrimaryKeyOffset()
            ->persist();

        foreach ($cities as $city) {
            $this->assertIsInt($city->id);
        }

        for ($i = 0; $i < $n; $i++) {
            $this->assertNotSame($cityOffset + $i, $cities[$i]->id);
            $this->assertIsInt($cities[$i]->id);
        }
    }

    /**
     * @dataProvider dataForTestDisablePrimaryKeyOffset
     * @param int $countryOffset
     */
    public function testDisablePrimaryKeyOffsetInAssociation(int $countryOffset)
    {
        $n = 5;
        $cities = CityFactory::make($n)
            ->with(
                'Country',
                CountryFactory::make()->setPrimaryKeyOffset($countryOffset)->disablePrimaryKeyOffset()
            )
            ->persist();

        $cityOffset = $cities[0]->id;

        for ($i = 0; $i < $n; $i++) {
            $this->assertSame($cityOffset + $i, $cities[$i]->id);
            $this->assertNotSame($countryOffset + $i, $cities[$i]->country->id);
            $this->assertIsInt($cities[$i]->country->id);
        }
    }

    public function testDisablePrimaryKeyOffsetInAssociationAndBase()
    {
        $nCities = rand(3, 5);
        $cityOffset = rand(1, 100000);
        $countryOffset = rand(1, 100000);

        $cities = CityFactory::make($nCities)
            ->with(
                'Country',
                CountryFactory::make()->setPrimaryKeyOffset($countryOffset)->disablePrimaryKeyOffset()
            )
            ->setPrimaryKeyOffset($cityOffset)
            ->disablePrimaryKeyOffset()
            ->persist();

        $this->assertNotSame($cityOffset + $nCities - 1, $cities[$nCities - 1]->id);
        $this->assertIsInt($cities[$nCities - 1]->id);
        $this->assertNotSame($countryOffset + $nCities - 1, $cities[$nCities - 1]->country->id);
        $this->assertIsInt($cities[$nCities - 1]->country->id);
    }

    public function testDisablePrimaryKeyOffsetInMultipleAssociationAndBase()
    {
        $nCities = rand(3, 5);
        $cityOffset = rand(1, 100000);
        $countryOffset = rand(1, 100000);

        /** @var Country $country */
        $country = CountryFactory::make()
            ->with(
                'Cities',
                CityFactory::make($nCities)->setPrimaryKeyOffset($cityOffset)->disablePrimaryKeyOffset()
            )
            ->setPrimaryKeyOffset($countryOffset)
            ->disablePrimaryKeyOffset()
            ->persist();

        $this->assertNotSame($countryOffset, $country->id);
        $this->assertIsInt($country->id);
        $this->assertNotSame($cityOffset + $nCities - 1, $country->cities[$nCities - 1]->id);
        $this->assertIsInt($country->cities[$nCities - 1]->id);
    }

    public function testDisablePrimaryKeyOffsetOnMultipleCalls()
    {
        /** @var \TestApp\Model\Entity\Country $country1 */
        $country1 = CountryFactory::make()->persist();
        /** @var \TestApp\Model\Entity\Country $country2 */
        $country2 = CountryFactory::make()->disablePrimaryKeyOffset()->persist();
        /** @var \TestApp\Model\Entity\Country $country3 */
        $country3 = CountryFactory::make()->disablePrimaryKeyOffset()->persist();

        $this->assertNotSame($country1->id, $country2->id);
        $this->assertNotSame($country2->id, $country3->id);
        $this->assertNotSame($country1->id, $country3->id);
        $this->assertIsInt($country1->id);
        $this->assertIsInt($country2->id);
        $this->assertIsInt($country3->id);
    }

    public function testDisablePrimaryOffsetOnMultipleCallsInAssociations()
    {
        $nCitiesPerCountry = rand(3, 5);
        $nCountries = rand(3, 5);
        $iterations = rand(3, 5);

        $factory = CountryFactory::make($nCountries)
            ->with('Cities', CityFactory::make($nCitiesPerCountry)->disablePrimaryKeyOffset())
            ->disablePrimaryKeyOffset();

        $countries = [];
        for ($i = 0; $i < $iterations; $i++) {
            $countries = $factory->persist();
        }

        foreach ($countries as $country) {
            $this->assertIsInt($country->id);
        }
    }

    public function testDisablePrimaryKeyOffsetWithCollectedAssociation()
    {
        $country = CountryFactory::make()
            ->with('Cities', CityFactory::make()->disablePrimaryKeyOffset())
            ->with('Cities', CityFactory::make()->disablePrimaryKeyOffset())
            ->persist();

        $this->assertNotSame($country->cities[0]->id, $country->cities[1]->id);
        $this->assertIsInt($country->cities[0]->id);
        $this->assertIsInt($country->cities[1]->id);
    }

    public function testDisablePrimaryKeyOffsetWithPrimaryKeyManually()
    {
        $id = 2;
        /** @var \TestApp\Model\Entity\Country $country */
        $country = CountryFactory::make()->patchData(compact('id'))->disablePrimaryKeyOffset()->persist();
        $this->assertSame($id, $country->id);

        $id = rand(1, 100000);
        /** @var \TestApp\Model\Entity\Country $country */
        $country = CountryFactory::make()->patchData(compact('id'))->disablePrimaryKeyOffset()->persist();
        $this->assertSame($id, $country->id);
    }

    public function testDisablePrimaryKeyOffsetWithPrimaryKeyManuallyInPlugin()
    {
        $id = 2;
        /** @var \TestPlugin\Model\Entity\Bill $bill */
        $bill = BillFactory::make()->patchData(compact('id'))->disablePrimaryKeyOffset()->persist();
        $this->assertSame($id, $bill->id);

        $id = rand(1, 100000);
        /** @var \TestPlugin\Model\Entity\Bill $bill */
        $bill = BillFactory::make()->patchData(compact('id'))->disablePrimaryKeyOffset()->persist();
        $this->assertSame($id, $bill->id);
    }
}
