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


use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\Error\UniquenessException;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;

class BaseFactoryUniqueEntitiesTest extends TestCase
{
    /**
     * @var \TestApp\Model\Table\CountriesTable
     */
    public $Countries;

    /**
     * @var \TestApp\Model\Table\CitiesTable
     */
    public $Cities;

    /**
     * @var \TestApp\Model\Table\AuthorsTable
     */
    public $Authors;

    /**
     * @var \TestApp\Model\Table\AddressesTable
     */
    public $Addresses;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function setUp(): void
    {
        $this->Addresses = TableRegistry::getTableLocator()->get('Addresses');
        $this->Authors = TableRegistry::getTableLocator()->get('Authors');
        $this->Countries = TableRegistry::getTableLocator()->get('Countries');
        $this->Cities = TableRegistry::getTableLocator()->get('Cities');
    }

    public function tearDown(): void
    {
        unset($this->Addresses);
        unset($this->Authors);
        unset($this->Cities);
        unset($this->Countries);
    }

    public function testGetUniqueProperties()
    {
        $this->assertSame(
            ['unique_stamp'],
            CountryFactory::make()->getUniqueProperties()
        );
        $this->assertSame(
            [],
            AuthorFactory::make()->getUniqueProperties()
        );
    }

    public function testDetectDuplicateAndThrowErrorWhenPrimary()
    {
        $this->expectException(PersistenceException::class);
        $unique_stamp = 'Foo';
        CountryFactory::make(compact('unique_stamp'))->persist();
        CountryFactory::make(compact('unique_stamp'))->persist();
    }

    public function testSaveEntitiesWithTheSameId()
    {
        $this->expectException(PersistenceException::class);
        AuthorFactory::make(['id' => 1])->persist();
        AuthorFactory::make(['id' => 1])->persist();
    }

    public function testNoUniquenessCreatesMultipleEntities()
    {
        $nCities = 3;
        CityFactory::make($nCities)->with('Country')->persist();
        $this->assertSame($nCities, $this->Cities->find()->count());
        $this->assertSame($nCities, $this->Countries->find()->count());
    }

    public function testDetectDuplicateInAssociation()
    {
        $unique_stamp = 'Foo';
        $originalCountry = CountryFactory::make([
            'unique_stamp' => $unique_stamp,
            'name' => 'First save',
        ])->persist();

        $city = CityFactory::make()->withCountry([
            'unique_stamp' => $unique_stamp,
            'name' => 'Second save',
        ])->persist();

        $newCountry = $city->get('country');

        $this->assertSame($originalCountry->id, $newCountry->id);
        $this->assertSame($city->get('country_id'), $newCountry->id);
        $this->assertSame($originalCountry->unique_stamp, $unique_stamp);
        $this->assertSame($newCountry->unique_stamp, $unique_stamp);
        $this->assertSame(1, $this->Countries->find()->count());
    }

    /**
     * @Given an author is created
     * @When an article with that same author is created
     * @Then the author is not created again, but updated.
     */
    public function testDetectDuplicatePrimaryKeyInAssociation()
    {
        $authorId = rand();
        $originalAuthor = AuthorFactory::make([
            'id' => $authorId,
        ])->persist();

        $authorName = 'Foo';
        $article = ArticleFactory::make()->with('Authors', [
            'id' => $authorId,
            'name' => $authorName
        ])->persist();

        $newAuthor = $article->get('authors')[0];

        $this->assertSame($originalAuthor->id, $newAuthor->id);
        $this->assertSame($authorName, $newAuthor->name);
        $this->assertSame(1, $this->Authors->find()->count());
        $this->assertSame(1, $this->Addresses->find()->count());
    }

    /**
     * @Given we instantiate a factory with a unique field
     * with two entries.
     * @When we get entities
     * @Then An Exception is thrown
     */
    public function testDetectDuplicateInInstantiation()
    {
        $this->expectException(UniquenessException::class);
        $factoryName = CountryFactory::class;
        $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of unique_stamp was not respected.");

        $unique_stamp = 'Foo';

        CountryFactory::make([
            compact('unique_stamp'),
            compact('unique_stamp'),
        ])->getEntities();
    }

    /**
     * @Given we instantiate a factory with a unique field
     * with two entries.
     * @When we get entities
     * @Then An Exception is thrown
     *
     * @throws \Exception
     */
    public function testDetectDuplicateInInstantiationWithTimes()
    {
        $this->expectException(UniquenessException::class);
        $factoryName = CountryFactory::class;
        $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of unique_stamp was not respected.");

        $unique_stamp = 'Foo';

        CountryFactory::make(compact('unique_stamp'), 2)->getEntities();
    }

    /**
     * @Given we instantiate a factory with a unique field
     * with two entries.
     * @When we get entities
     * @Then An Exception is thrown.
     */
    public function testDetectDuplicateInPatchWithTimes()
    {
        $this->expectException(UniquenessException::class);
        $factoryName = CountryFactory::class;
        $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of unique_stamp was not respected.");

        $unique_stamp = 'Foo';

        CountryFactory::make( 2)->patchData(compact('unique_stamp'))->getEntities();
    }

    /**
     * @Given we instantiate a factory with a unique field
     * with two entries.
     * @When we persist
     * @Then An Exception is thrown.
     */
    public function testDetectDuplicateInInstantiationPersist()
    {
        $this->expectException(UniquenessException::class);
        $factoryName = CountryFactory::class;
        $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of unique_stamp was not respected.");

        $unique_stamp = 'Foo';

        CountryFactory::make([
            compact('unique_stamp'),
            compact('unique_stamp'),
        ])->persist();
    }

    /**
     * @Given we instantiate an associated factory with a unique field
     * with two entries
     * @When we get entities
     * @Then An exception is thrown.
     *
     * @throws \Exception
     */
    public function testDetectDuplicateInInstantiationWithTimesInAssociation()
    {
        $this->expectException(UniquenessException::class);
        $factoryName = CityFactory::class;
        $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of virtual_unique_stamp was not respected.");

        $virtual_unique_stamp = 'virtual_unique_stamp';

        CountryFactory::make()->with('Cities', [
            compact('virtual_unique_stamp'),
            compact('virtual_unique_stamp'),
        ])->getEntities();
    }

    /**
     * @Given we instantiate an associated factory with a unique field
     * with two entries provided by numerically
     * @When we get entities
     * @Then An exception is thrown.
     *
     * @throws \Exception
     */
    public function testDetectDuplicateInInstantiationWithTimesInAssociationNumeric()
    {
        $this->expectException(UniquenessException::class);
        $factoryName = CityFactory::class;
        $this->expectExceptionMessage("Error in {$factoryName}. The uniqueness of virtual_unique_stamp was not respected.");

        $virtual_unique_stamp = 'virtual_unique_stamp';

        CountryFactory::make()->with('Cities[2]', compact('virtual_unique_stamp'))->getEntities();
    }

    /**
     * @Given we create n countries with a common cities (imagine...)
     * @When we persist
     * @Then only on single city should be persisted and be associated
     * to all n countries.
     */
    public function testCreateSeveralEntitiesWithSameAssociationHasMany()
    {
        $virtual_unique_stamp = 'foo';

        // HasMany
        $nCountries = 3;
        $countries = CountryFactory::make($nCountries)
            ->with('Cities', compact('virtual_unique_stamp'))
            ->persist();

        $this->assertSame(1, $this->Cities->find()->count());
        $this->assertSame($nCountries, $this->Countries->find()->count());
        $cityId = $this->Cities->find()->first()->id;
        foreach ($countries as $country) {
            $this->assertSame($virtual_unique_stamp, $country->cities[0]->virtual_unique_stamp);
            $this->assertSame($cityId, $country->cities[0]->id);
        }
    }

    /**
     * @Given we create n cities within a country
     * @When we persist
     * @Then only on single country should be persisted and be associated
     * to all n cities.
     */
    public function testCreateSeveralEntitiesWithSameAssociationBelongsTo()
    {
        $unique_stamp = 'foo';

        // BelongsTo
        $nCities = 3;
        $cities = CityFactory::make($nCities)
            ->with('Country', compact('unique_stamp'))
            ->persist();

        $this->assertSame(1, $this->Countries->find()->count());
        $this->assertSame($nCities, $this->Cities->find()->count());
        $countryId = $this->Countries->find()->first()->id;
        foreach ($cities as $city) {
            $this->assertSame( $unique_stamp, $city->country->unique_stamp);
            $this->assertSame($countryId, $city->country_id);
        }
    }

    /**
     * @Given we create n cities within a country
     * @When we persist
     * @Then only on single country should be persisted and be associated
     * to all n cities.
     */
    public function testCreateSeveralEntitiesWithSameAssociationBelongsToWithChainedWith()
    {
        $unique_stamp = 'foo';

        // BelongsTo
        $nCities = 3;
        $countryName = 'Foo';
        $cities = CityFactory::make($nCities)
            ->with('Country', compact('unique_stamp'))
            ->with('Country', compact('unique_stamp') + ['name' => $countryName])
            ->persist();

        $this->assertSame(1, $this->Countries->find()->count());
        $this->assertSame($nCities, $this->Cities->find()->count());
        $retrievedCountry = $this->Countries->find()->first();
        $countryId = $retrievedCountry->id;
        $this->assertSame($countryName, $retrievedCountry->name);
        foreach ($cities as $city) {
            $this->assertSame( $unique_stamp, $city->country->unique_stamp);
            $this->assertSame($countryId, $city->country_id);
        }
    }
    public function getFixtures(): array
    {
        return parent::getFixtures(); // TODO: Change the autogenerated stub
    }
}