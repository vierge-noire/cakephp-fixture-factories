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
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use CakephpFixtureFactories\Error\AssociationBuilderException;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\CustomerFactory;
use CakephpFixtureFactories\Util;
use Exception;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\City;
use TestApp\Model\Entity\Country;
use TestApp\Model\Entity\PremiumAuthor;
use TestApp\Model\Table\PremiumAuthorsTable;
use TestPlugin\Model\Entity\Bill;
use TestPlugin\Model\Entity\Customer;

class BaseFactoryAssociationsTest extends TestCase
{
    /**
     * @var \TestApp\Model\Table\AuthorsTable
     */
    private $AuthorsTable;

    /**
     * @var \TestApp\Model\Table\AddressesTable
     */
    private $AddressesTable;

    /**
     * @var \TestApp\Model\Table\ArticlesTable
     */
    private $ArticlesTable;

    /**
     * @var \TestApp\Model\Table\CountriesTable
     */
    private $CountriesTable;

    /**
     * @var \TestApp\Model\Table\CitiesTable
     */
    private $CitiesTable;

    /**
     * @var \TestPlugin\Model\Table\CustomersTable
     */
    private $CustomersTable;

    /**
     * @var \TestPlugin\Model\Table\BillsTable
     */
    private $BillsTable;

    public static function setUpBeforeClass()
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass()
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function setUp()
    {
        $this->AuthorsTable     = TableRegistry::getTableLocator()->get('Authors');
        $this->AddressesTable   = TableRegistry::getTableLocator()->get('Addresses');
        $this->ArticlesTable    = TableRegistry::getTableLocator()->get('Articles');
        $this->CountriesTable   = TableRegistry::getTableLocator()->get('Countries');
        $this->CitiesTable      = TableRegistry::getTableLocator()->get('Cities');
        $this->BillsTable       = TableRegistry::getTableLocator()->get('TestPlugin.Bills');
        $this->CustomersTable   = TableRegistry::getTableLocator()->get('TestPlugin.Customers');

        parent::setUp();
    }

    public function tearDown()
    {
        Configure::delete('TestFixtureNamespace');
        unset($this->AuthorsTable);
        unset($this->AddressesTable);
        unset($this->ArticlesTable);
        unset($this->CountriesTable);
        unset($this->CitiesTable);
        unset($this->BillsTable);
        unset($this->CustomersTable);

        parent::tearDown();
    }

    public function testWithMultipleAssociations()
    {
        $n = 5;
        $street = 'FooStreet';
        $article = ArticleFactory::make()
            ->with("Authors[$n].Address", compact('street'))
            ->persist();

        $this->assertSame($n, count($article->authors));
        foreach ($article->authors as $author) {
            $this->assertSame($street, $author->address->street);
        }
        $this->assertSame($n, $this->AuthorsTable->find()->count());
    }

    public function testWithMultipleHasOneExeption()
    {
        $this->expectException(AssociationBuilderException::class);
        ArticleFactory::make()
            ->with("Authors.Address[2]")
            ->getEntity();
    }

    public function testWithMultipleAssociationsDeep()
    {
        $nAuthors = 3;
        $mArticles = 5;
        $article = ArticleFactory::make()
            ->with("Authors[$nAuthors].Articles[$mArticles].Bills", BillFactory::make()->without('Article'))
            ->persist();

        $authors = $article->authors;
        $this->assertSame($nAuthors, count($authors));
        foreach ($article->authors as $author) {
            $this->assertSame($mArticles, count($author->articles));
            foreach ($author->articles as $article) {
                $this->assertSame(1, count($article->bills));
            }
        }

        $expectedAuthors = $nAuthors * ($mArticles * 2 + 1);
        $this->assertSame(
            $expectedAuthors,
            $this->AuthorsTable->find()->count()
        );

        $expectedArticles = 1 + ($nAuthors * $mArticles);
        $this->assertSame(
            $expectedArticles,
            $this->ArticlesTable->find()->count()
        );
    }

    public function testSaveMultipleInArray()
    {
        $name1 = 'Foo';
        $name2 = 'Bar';
        $countries = CountryFactory::make([
            ['name' => $name1],
            ['name' => $name2],
        ])->persist();

        $this->assertSame(2, $this->CountriesTable->find()->count());
        $this->assertSame($name1, $countries[0]->name);
        $this->assertSame($name2, $countries[1]->name);
        $this->assertSame($name1, $this->CountriesTable->get($countries[0]->id)->name);
        $this->assertSame($name2, $this->CountriesTable->get($countries[1]->id)->name);
    }

    public function testSaveMultipleInArrayWithTimes()
    {
        $times = 2;
        $name1 = 'Foo';
        $name2 = 'Bar';
        $countries = CountryFactory::make([
            ['name' => $name1],
            ['name' => $name2],
        ], $times)->persist();

        $this->assertSame($times * 2, $this->CountriesTable->find()->count());

        $this->assertSame($name1, $countries[0]->name);
        $this->assertSame($name2, $countries[1]->name);
        $this->assertSame($name1, $countries[2]->name);
        $this->assertSame($name2, $countries[3]->name);
        $this->assertSame($name1, $this->CountriesTable->get($countries[0]->id)->name);
        $this->assertSame($name2, $this->CountriesTable->get($countries[1]->id)->name);
        $this->assertSame($name1, $this->CountriesTable->get($countries[2]->id)->name);
        $this->assertSame($name2, $this->CountriesTable->get($countries[3]->id)->name);
    }

    public function testSaveMultipleHasManyAssociation()
    {
        $amount1 = rand(1, 10000);
        $amount2 = rand(1, 10000);
        $customer = CustomerFactory::make()
            ->withBills([
                ['amount' => $amount1],
                ['amount' => $amount2],
            ])->persist();

        $this->assertSame(2, $this->BillsTable->find()->count());
        $this->assertEquals($amount1, $customer->bills[0]->amount);
        $this->assertEquals($amount2, $customer->bills[1]->amount);

        $customer = $this->CustomersTable->findById($customer->id)->contain('Bills')->firstOrFail();
        $this->assertEquals($amount1, $customer->bills[0]->amount);
        $this->assertEquals($amount2, $customer->bills[1]->amount);
    }

    public function testSaveMultipleHasManyAssociationAndTimes()
    {
        $times = 2;
        $amount1 = rand(1, 10000);
        $amount2 = rand(1, 10000);
        $customer = CustomerFactory::make()
            ->withBills([
                ['amount' => $amount1],
                ['amount' => $amount2],
            ], $times)->persist();

        $this->assertSame(2 * $times, $this->BillsTable->find()->count());
        $this->assertEquals($amount1, $customer->bills[0]->amount);
        $this->assertEquals($amount2, $customer->bills[1]->amount);
        $this->assertEquals($amount1, $customer->bills[2]->amount);
        $this->assertEquals($amount2, $customer->bills[3]->amount);

        $bills = $this->BillsTable->find()->toArray();
        $this->assertEquals($amount1, $bills[0]->amount);
        $this->assertEquals($amount2, $bills[1]->amount);
        $this->assertEquals($amount1, $bills[2]->amount);
        $this->assertEquals($amount2, $bills[3]->amount);
    }

    public function testGetAssociatedFactoryWithOneDepth()
    {
        $street = 'Foo';
        $author = AuthorFactory::make()->with('BusinessAddress', [
            'street' => $street,
        ])->persist();

        $this->assertInstanceOf(Address::class, $author->business_address);

        $author = $this->AuthorsTable->findById($author->id)->contain('BusinessAddress')->firstOrFail();
        $this->assertSame($street, $author->business_address->street);

        // There should now be two addresses in the DB
        $this->assertSame(2, $this->AddressesTable->find()->count());
    }

    public function testGetAssociatedFactoryWithMultipleDepth()
    {
        $country = 'Foo';
        $path = 'BusinessAddress.City.Country';
        $author = AuthorFactory::make()->with($path, [
            'name' => $country,
        ])->persist();

        $this->assertInstanceOf(Country::class, $author->business_address->city->country);

        $author = $this->AuthorsTable->findById($author->id)->contain(['BusinessAddress.City.Country'])->firstOrFail();
        $this->assertSame($country, $author->business_address->city->country->name);

        // There should now be two addresses in the DB
        $this->assertSame(2, $this->AddressesTable->find()->count());
    }

    public function testGetAssociatedFactoryWithMultipleDepthWithFactory()
    {
        $city = 'Foo';

        $author = AuthorFactory::make()->with(
            'BusinessAddress.City',
            CityFactory::make(['name' => $city])
        )->persist();

        $this->assertInstanceOf(City::class, $author->business_address->city);

        $author = $this->AuthorsTable->findById($author->id)->contain(['BusinessAddress.City'])->firstOrFail();
        $this->assertSame($city, $author->business_address->city->name);
    }

    public function testGetAssociatedFactoryWithMultipleDepthAndMultipleTimes()
    {
        $n = 10;
        $country = 'Foo';
        $path = 'BusinessAddress.City.Country';
        $authors = AuthorFactory::make($n)->with($path, [
            'name' => $country,
        ])->persist();

        for ($i = 0; $i < $n; $i++) {
            $this->assertInstanceOf(Country::class, $authors[$i]->business_address->city->country);
        }

        $authors = $this->AuthorsTable->find()->contain($path);
        foreach ($authors as $author) {
            $this->assertSame($country, $author->business_address->city->country->name);
        }

        // There should now be $n * 2 addresses in the DB
        $this->assertSame(2 * $n, $this->AddressesTable->find()->count());
    }

    public function testGetAssociatedFactoryWithMultipleDepthInPlugin()
    {
        $name = 'Foo';
        $path = 'Bills.Customer';
        $article = ArticleFactory::make()->with($path, compact('name'))->persist();

        $this->assertInstanceOf(Customer::class, $article->bills[0]->customer);

        $this->assertSame(1, $this->ArticlesTable->find()->count());
        $this->assertSame(1, $this->CustomersTable->find()->count());

        $article = $this->ArticlesTable->findById($article->id)->contain($path)->firstOrFail();
        $this->assertSame($name, $article->bills[0]->customer->name);
    }

    public function testGetAssociatedFactoryInPluginWithNumber()
    {
        $n = 10;
        $article = ArticleFactory::make()->with('Bills', $n)->persist();

        $this->assertInstanceOf(Bill::class, $article->bills[0]);

        $bills = $this->BillsTable->find();
        $this->assertSame($n, $bills->count());
    }

    public function testGetAssociatedFactoryInPluginWithMultipleConstructs()
    {
        $n = 10;
        $article = ArticleFactory::make()->with('Bills', BillFactory::make($n)->with('Customer'))->persist();

        $this->assertInstanceOf(Bill::class, $article->bills[0]);
        $this->assertInstanceOf(Customer::class, $article->bills[0]->customer);

        $this->assertSame(
            $n,
            $this->BillsTable->find()->count()
        );

        $this->assertSame(
            $n,
            $this->CustomersTable->find()->count()
        );
    }

    public function testSaveMultipleHasManyAssociationAndTimesWithBrackets()
    {
        $times = 5;
        $street1 = 'Station Street';
        $street2 = 'Baker Street';

        // Create a country with $times cities, all having two streets of a fixed name
        $country = CountryFactory::make()->with("Cities[$times].Addresses", [
            ['street' => $street1],
            ['street' => $street2],
        ])->persist();

        $this->assertSame(2 * $times, $this->AddressesTable->find()->count());
        $country = $this->CountriesTable->findById($country->id)->contain('Cities.Addresses')->first();

        for ($i = 0; $i < $times; $i++) {
            $this->assertEquals($street1, $country->cities[$i]->addresses[0]->street);
            $this->assertEquals($street2, $country->cities[$i]->addresses[1]->street);
        }
    }

    public function testGetAssociatedFactoryWithReversedAssociation()
    {
        $name1 = 'Bar';
        $name2 = 'Foo';
        AuthorFactory::make(['name' => $name1])
            ->with('Articles.Authors', ['name' => $name2])
            ->persist();

        $authors = $this->ArticlesTable
            ->find()
            ->contain('Authors', function ($q) {
                return $q->order('Authors.name');
            })
            ->first()
            ->authors;
        $this->assertSame($name1, $authors[0]->name);
        $this->assertSame($name2, $authors[1]->name);
    }

    public function testGetAssociatedFactoryWithMultipleDepthAndWithout()
    {
        $author = AuthorFactory::make()
            ->with('BusinessAddress.City.Country')
            ->with('BusinessAddress.City')
            ->without('BusinessAddress')
            ->persist();

        $this->assertNull($author->business_address);
        $this->assertNull($this->AuthorsTable->findById($author->id)->contain('BusinessAddress')->firstOrFail()->business_address);

        // There should be only one address, city and country in the DB
        $this->assertSame(
            1,
            $this->AddressesTable->find()->count()
        );
        $this->assertSame(
            1,
            $this->CitiesTable->find()->count()
        );
        $this->assertSame(
            1,
            $this->CountriesTable->find()->count()
        );
    }

    public function testSaveMultiplesToOneAssociationShouldSaveOnlyOne()
    {
        $city = CityFactory::make()->with('Country', [
            ['name' => 'Foo1'],
            ['name' => 'Foo2'],
            ['name' => 'Foo3'],
            ['name' => 'Foo4'],
        ])->persist();

        $city = $this->CitiesTable->get($city->id, ['contain' => 'Country']);

        $this->assertSame('Foo1', $city->country->name);
        $this->assertSame(1, $this->CountriesTable->find()->count());
        $this->assertSame(1, $this->CitiesTable->find()->count());
    }

    public function testAssignWithoutToManyAssociation()
    {
        $countryExpected = 'Foo';
        $countryNotExpected = 'Bar';
        CountryFactory::make(['name' => $countryExpected])
            ->with('Cities', CityFactory::make()
                ->with('Country', ['name' => $countryNotExpected])
            )->persist();

        $this->assertSame(1, $this->CitiesTable->find()->count());
        $city = $this->CitiesTable->find()->contain('Country')->firstOrFail();
        $this->assertSame($countryExpected, $city->country->name);
    }

    /*
     * The created city is associated with a country, which on the
     * flies get $n cities assigned. We make sure that the first city
     * is correctly associated to the country
     */
    public function testAssignWithToManyAssociation()
    {
        $nCities = rand(3, 10);
        $city = CityFactory::make()
            ->with('Country', CountryFactory::make()->with('Cities', $nCities))
            ->persist();

        $citiesAssociatedToCountry = $this->CountriesTable
            ->findById($city->country_id)
            ->contain(['Cities'])
            ->firstOrFail()
            ->cities;

        $this->assertSame($nCities + 1, count($citiesAssociatedToCountry));
        $citiesNameList = Hash::extract($citiesAssociatedToCountry, '{n}.name');
        $this->assertTrue(in_array($city->name, $citiesNameList));
    }

    /*
     * The same as above, but with belongsToMany association
     */
    public function testAssignWithBelongsToManyAssociation()
    {
        $nArticles = rand(3, 10);
        $authorName = 'Foo';
        $article = ArticleFactory::make()
            ->with('Authors', AuthorFactory::make(['name' => 'Foo'])->with('Articles', $nArticles))
            ->persist();

        $authorsAssociatedToArticle = $this->AuthorsTable
            ->find()
            ->matching('Articles', function ($q) use ($article) {
                return $q->where(['Articles.id' => $article->id]);
            })
            ->contain('Articles');

        $articlesAssociatedToAuthor = $this->ArticlesTable
            ->find()
            ->matching('Authors', function ($q) use ($authorName) {
                return $q->where(['Authors.name' => $authorName]);
            });

        $this->assertSame($nArticles + 1, $articlesAssociatedToAuthor->count());
        $this->assertSame(1, $authorsAssociatedToArticle->count());
    }

    public function testArticleWithPremiumAuthors()
    {
        $nPremiumAuthors = rand(2, 5);
        $article = ArticleFactory::make()
            ->with('ExclusivePremiumAuthors', $nPremiumAuthors)
            ->without('Authors')
            ->persist();

        $alias = PremiumAuthorsTable::ASSOCIATION_ALIAS;
        $this->assertTrue(is_array($article[$alias]));
        foreach ($article[$alias] as $author) {
            $this->assertInstanceOf(PremiumAuthor::class, $author);
            $this->assertTrue(is_int($author->id));
        }
        $this->assertSame($nPremiumAuthors, $this->AuthorsTable->find()->count());
    }

    public function testCountryWith2CitiesEachOfThemWith2DifferentAddresses()
    {
        $street1 = 'street1';
        $street2 = 'street2';
        CountryFactory::make()->with('Cities[2].Addresses', [
            ['street' => $street1],
            ['street' => $street2],
        ])->persist();

        $country = $this->CountriesTable
            ->find()
            ->contain('Cities.Addresses')
            ->firstOrFail();

        $this->assertSame(2, count($country->cities));
        foreach ($country->cities as $city) {
            $this->assertSame(2, count($city->addresses));
            $this->assertSame($street1, $city->addresses[0]->street);
            $this->assertSame($street2, $city->addresses[1]->street);
        }
    }

    public function testCountryWith2CitiesEachOfThemWithADifferentSpecifiedAddress()
    {
        $country = CountryFactory::make()->persist();
        $street1 = 'street1';
        $street2 = 'street2';
        AddressFactory::make([
            ['street' => $street1],
            ['street' => $street2],
        ])->with('City', CityFactory::make(['country_id' => $country->id])->without('Country'))
        ->persist();

        $country = $this->CountriesTable
            ->get($country->id, [
                'contain' => 'Cities.Addresses',
            ]);

        $this->checkCountryWithTwoCitiesEachWithOneAddress($country, $street1, $street2);
    }

    private function checkCountryWithTwoCitiesEachWithOneAddress(Country $country, string $street1, string $street2)
    {
        $this->assertSame(2, count($country->cities));
        foreach ($country->cities as $city) {
            $this->assertSame(1, count($city->addresses));
        }
        $this->assertSame($street1, $country->cities[0]->addresses[0]->street);
        $this->assertSame($street2, $country->cities[1]->addresses[0]->street);
    }

    public function testCountryWith2CitiesEachOfThemWithADifferentSpecifiedAddressTheOtherWay()
    {
        $street1 = 'A street';
        $street2 = 'B street';

        $country = CountryFactory::make()
            ->with('Cities.Addresses', ['street' => $street1])
            ->with('Cities.Addresses', ['street' => $street2])
            ->persist();

        $this->checkCountryWithTwoCitiesEachWithOneAddress($country, $street1, $street2);

        // Make sure that all was correctly persisted
        $addresses = $this->AddressesTable->find()
            ->innerJoinWith('City.Country', function (Query $q) use ($country) {
                return $q->where(['Country.id' => $country->id]);
            })
            ->orderAsc('street')
            ->toArray();

        $this->assertSame(2, count($addresses));
        $this->assertSame($street1, $addresses[0]->street);
        $this->assertSame($street2, $addresses[1]->street);

        $this->assertTrue(abs($addresses[0]->id - $addresses[1]->id) > 1);
    }

    public function testCountryWith2Cities()
    {
        $city1 = 'A city';
        $city2 = 'B city';

        $country = CountryFactory::make()
            ->with('Cities', ['name' => $city1])
            ->with('Cities', ['name' => $city2])
            ->persist();

        // Make sure that all was correctly persisted
        $cities = $this->CitiesTable->find()
            ->where(['country_id' => $country->id])
            ->orderAsc('name')
            ->toArray();

        $this->assertSame(2, count($cities));
        $this->assertSame($city1, $cities[0]->name);
        $this->assertSame($city2, $cities[1]->name);
        $this->assertTrue(abs($cities[0]->id - $cities[1]->id) > 1);
    }

    public function testCountryWith4Cities()
    {
        $city1 = 'foo';
        $city2 = 'bar';
        $street1 = 'street1';
        $street2 = 'street2';

        $country = CountryFactory::make()
            ->with('Cities', ['id' => 1, 'name' => $city1])
            ->with('Cities', ['id' => 2, 'name' => $city2])
            ->with('Cities.Addresses', ['id' => 1, 'street' => $street1])
            ->with('Cities.Addresses', ['id' => 2, 'street' => $street2])
            ->persist();

        // Make sure that all was correctly persisted
        $country = $this->CountriesTable
            ->get($country->id, [
                'contain' => 'Cities',
            ]);

        $this->assertSame(4, count($country->cities));
        $this->assertSame(4, $this->CitiesTable->find()->count());

        if (Util::isRunningOnPostgresql(CountryFactory::make())) {
            $this->assertSame($city1, $this->CitiesTable->get(1)->name);
            $this->assertSame($city2, $this->CitiesTable->get(2)->name);
            $this->assertSame($street1, $this->AddressesTable->get(1)->street);
            $this->assertSame($street2, $this->AddressesTable->get(2)->street);
        }
    }

    /**
     * When an association has the same name as a virtual field,
     * the virtual field will overwrite the data prepared by the
     * associated factory
     *
     * @see Country::_getVirtualCities()
     * @throws Exception
     */
    public function testAssociationWithVirtualFieldNamedIdentically()
    {
        $factory = CountryFactory::make()
            ->with('Cities')
            ->with('VirtualCities');


        $country = $factory->getEntity();
        $this->assertSame(true, is_string($country->cities[0]->name));
        $this->assertSame(false, $country->virtual_cities);

        $country = $factory->persist();
        $this->assertSame(true, is_string($country->cities[0]->name));
        $this->assertSame(false, $country->virtual_cities);

        // Only the non virtual Cities will be saved
        $this->assertSame(1, $this->CitiesTable->find()->count());
        $this->assertSame(1, $this->CountriesTable->find()->count());
    }
}
