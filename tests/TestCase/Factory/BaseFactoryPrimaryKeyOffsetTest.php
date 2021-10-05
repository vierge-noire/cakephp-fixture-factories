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
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use TestApp\Model\Entity\Country;

class BaseFactoryPrimaryKeyOffsetTest extends TestCase
{
    use TruncateDirtyTables;

    public function dataForTestSetPrimaryKeyOffset()
    {
        return [
            [rand(1, 1000000)],
            [rand(1, 1000000)],
            [rand(1, 1000000)],
        ];
    }

    /**
     * @dataProvider dataForTestSetPrimaryKeyOffset
     * @param int $cityOffset
     */
    public function testSetPrimaryKeyOffset(int $cityOffset)
    {
        $n = 10;
        $cities = CityFactory::make($n)
            ->setPrimaryKeyOffset($cityOffset)
            ->persist();

        $countryOffset = $cities[0]->country->id;

        for ($i = 0; $i < $n; $i++) {
            $this->assertSame($cityOffset + $i, $cities[$i]->id);
            $this->assertSame($countryOffset + $i, $cities[$i]->country->id);
        }
    }

    /**
     * @dataProvider dataForTestSetPrimaryKeyOffset
     * @param int $countryOffset
     */
    public function testSetPrimaryKeyOffsetInAssociation($countryOffset)
    {
        $n = 5;
        $cities = CityFactory::make($n)
            ->with('Country', CountryFactory::make()->setPrimaryKeyOffset($countryOffset))
            ->persist();

        $cityOffset = $cities[0]->id;

        for ($i = 0; $i < $n; $i++) {
            $this->assertSame($cityOffset + $i, $cities[$i]->id);
            $this->assertSame($countryOffset + $i, $cities[$i]->country->id);
        }
    }

    public function testSetPrimaryKeyOffsetInAssociationAndBase()
    {
        $nCities = rand(3, 5);
        $cityOffset = rand(1, 100000);
        $countryOffset = rand(1, 100000);

        $cities = CityFactory::make($nCities)
            ->with('Country', CountryFactory::make()->setPrimaryKeyOffset($countryOffset))
            ->setPrimaryKeyOffset($cityOffset)
            ->persist();

        $this->assertSame($cityOffset + $nCities - 1, $cities[$nCities - 1]->id);
        $this->assertSame($countryOffset + $nCities - 1, $cities[$nCities - 1]->country->id);
    }

    public function testSetPrimaryKeyOffsetInMultipleAssociationAndBase()
    {
        $nCities = rand(3, 5);
        $cityOffset = rand(1, 100000);
        $countryOffset = rand(1, 100000);

        /** @var Country $country */
        $country = CountryFactory::make()
            ->with('Cities', CityFactory::make($nCities)->setPrimaryKeyOffset($cityOffset))
            ->setPrimaryKeyOffset($countryOffset)
            ->persist();

        $this->assertSame($countryOffset, $country->id);
        $this->assertSame($cityOffset + $nCities - 1, $country->cities[$nCities - 1]->id);
    }

    /**
     * Given a persisted country
     * If we create second country with the same id
     * The an exception should be thrown
     *
     * @throws \Exception
     */
    public function testSetPrimaryKeyOffsetConflict()
    {
        $country = CountryFactory::make()->persist();
        $offset = $country->id;

        $this->expectException(PersistenceException::class);
        CountryFactory::make()->setPrimaryKeyOffset($offset)->persist();
    }

    public function testPrimaryOffsetOnMultipleCalls()
    {
        $n = rand(3, 5);
        $m = rand(3, 5);
        $offset = rand(1, 1000000);
        $factory = CountryFactory::make($n)->setPrimaryKeyOffset($offset);

        $countries = [];
        for ($i = 0; $i < $m; $i++) {
            $countries = $factory->persist();
        }
        $lastCountryId = $countries[$n - 1]->id;
        $expectedId = $offset + $n * $m - 1;
        $this->assertSame($expectedId, $lastCountryId);
    }

    public function testPrimaryOffsetOnMultipleCallsInAssociations()
    {
        $nCitiesPerCountry = rand(3, 5);
        $nCountries = rand(3, 5);
        $cityOffset = rand(1, 1000000);
        $countryOffset = rand(1, 1000000);
        $iterations = rand(3, 5);

        $factory = CountryFactory::make($nCountries)
            ->with('Cities', CityFactory::make($nCitiesPerCountry)->setPrimaryKeyOffset($cityOffset))
            ->setPrimaryKeyOffset($countryOffset);

        $countries = [];
        for ($i = 0; $i < $iterations; $i++) {
            $countries = $factory->persist();
        }

        $lastCountryId = $countries[$nCountries - 1]->id;
        $expectedLastCountryId = $countryOffset + $nCountries * $iterations - 1;
        $this->assertSame($expectedLastCountryId, $lastCountryId);

        $lastCityId = $countries[$nCountries - 1]->cities[$nCitiesPerCountry - 1]->id;
        $expectedLastCityId = $cityOffset + $nCountries * $nCitiesPerCountry * $iterations - 1;
        $this->assertSame($expectedLastCityId, $lastCityId);
    }

    public function testTargetKeyOffsetWithCollectedAssociation()
    {
        $offset1 = rand(1, 100000);
        $offset2 = $offset1 + rand(1, 100);

        $country = CountryFactory::make()
            ->with('Cities', CityFactory::make()->setPrimaryKeyOffset($offset1))
            ->with('Cities', CityFactory::make()->setPrimaryKeyOffset($offset2))
            ->persist();

        $this->assertSame($offset1, $country->cities[0]->id);
        $this->assertSame($offset2, $country->cities[1]->id);
    }

    public function testSetPrimaryKeyManually()
    {
        $id = 2;
        $country = CountryFactory::make()->patchData(compact('id'))->persist();
        $this->assertSame($id, $country->id);

        $id = rand(1, 100000);
        $country = CountryFactory::make()->patchData(compact('id'))->persist();
        $this->assertSame($id, $country->id);
    }

    public function testSetPrimaryKeyManuallyInPlugin()
    {
        $id = 2;
        $bill = BillFactory::make()->patchData(compact('id'))->persist();
        $this->assertSame($id, $bill->id);

        $id = rand(1, 100000);
        $bill = BillFactory::make()->patchData(compact('id'))->persist();
        $this->assertSame($id, $bill->id);
    }
}
