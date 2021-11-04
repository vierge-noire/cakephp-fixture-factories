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

use Exception;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\City;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Country;
use TestPlugin\Model\Entity\Bill;
use Cake\Database\Driver\Postgres;
use TestPlugin\Model\Entity\Customer;
use TestApp\Model\Entity\PremiumAuthor;
use TestApp\Model\Table\PremiumAuthorsTable;
use CakephpFixtureFactories\ORM\FactoryTableRegistry;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CatFactory;
use CakephpFixtureFactories\Test\Factory\DogFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\CustomerFactory;
use CakephpFixtureFactories\Error\AssociationBuilderException;

class BaseFactoryAssociationsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function testMultipleDeepAssociationWithCircular()
    {
        $country = CountryFactory::make()->persist();
        $city = CityFactory::make()->with('Country', $country)->persist();
        $cat = CatFactory::make()->with('Country', $country)->persist();
        $dog = DogFactory::make()->with('Country', $country)->persist();

        $this->assertSame($country->id, $city->country_id);
        $this->assertSame($country->id, $cat->country->id);
        $this->assertSame($country->id, $dog->country->id);

        AddressFactory::make()
            ->with('City', $city)
            ->with('Rooms', [
                'Cats' => $cat,
                'Dogs' => $dog,
            ])
            ->persist();

        $this->assertSame(1, CityFactory::count());
        $this->assertSame(1, CountryFactory::count());
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
        $this->assertSame($n, AuthorFactory::count());
    }

    public function testWithMultipleHasOneExeption()
    {
        $this->expectException(AssociationBuilderException::class);
        ArticleFactory::make()
            ->with('Authors.Address[2]')
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
            AuthorFactory::count()
        );

        $expectedArticles = 1 + ($nAuthors * $mArticles);
        $this->assertSame(
            $expectedArticles,
            ArticleFactory::count()
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

        $this->assertSame(2, CountryFactory::count());
        $this->assertSame($name1, $countries[0]->name);
        $this->assertSame($name2, $countries[1]->name);
        $this->assertSame($name1, CountryFactory::get($countries[0]->id)->name);
        $this->assertSame($name2, CountryFactory::get($countries[1]->id)->name);
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

        $this->assertSame($times * 2, CountryFactory::count());

        $this->assertSame($name1, $countries[0]->name);
        $this->assertSame($name2, $countries[1]->name);
        $this->assertSame($name1, $countries[2]->name);
        $this->assertSame($name2, $countries[3]->name);
        $this->assertSame($name1, CountryFactory::get($countries[0]->id)->name);
        $this->assertSame($name2, CountryFactory::get($countries[1]->id)->name);
        $this->assertSame($name1, CountryFactory::get($countries[2]->id)->name);
        $this->assertSame($name2, CountryFactory::get($countries[3]->id)->name);
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

        $this->assertSame(2, BillFactory::count());
        $this->assertEquals($amount1, $customer->bills[0]->amount);
        $this->assertEquals($amount2, $customer->bills[1]->amount);

        /** @var Customer $customer */
        $customer = CustomerFactory::get($customer->id, ['contain' => 'Bills']);
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

        $this->assertSame(2 * $times, BillFactory::count());
        $this->assertEquals($amount1, $customer->bills[0]->amount);
        $this->assertEquals($amount2, $customer->bills[1]->amount);
        $this->assertEquals($amount1, $customer->bills[2]->amount);
        $this->assertEquals($amount2, $customer->bills[3]->amount);

        $bills = BillFactory::find()->toArray();
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

        $author = AuthorFactory::get($author->id, ['contain' => 'BusinessAddress']);
        $this->assertSame($street, $author->business_address->street);

        // There should now be two addresses in the DB
        $this->assertSame(2, AddressFactory::count());
    }

    public function testGetAssociatedFactoryWithMultipleDepth()
    {
        $country = 'Foo';
        $path = 'BusinessAddress.City.Country';
        $author = AuthorFactory::make()->with($path, [
            'name' => $country,
        ])->persist();

        $this->assertInstanceOf(Country::class, $author->business_address->city->country);

        $author = AuthorFactory::get($author->id, ['contain' => ['BusinessAddress.City.Country']]);
        $this->assertSame($country, $author->business_address->city->country->name);

        // There should now be two addresses in the DB
        $this->assertSame(2, AddressFactory::count());
    }

    public function testGetAssociatedFactoryWithMultipleDepthWithFactory()
    {
        $city = 'Foo';

        $author = AuthorFactory::make()->with(
            'BusinessAddress.City',
            CityFactory::make(['name' => $city])
        )->persist();

        $this->assertInstanceOf(City::class, $author->business_address->city);

        $author = AuthorFactory::get($author->id, ['contain' => 'BusinessAddress.City']);
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

        $authors = AuthorFactory::find()->contain($path);
        foreach ($authors as $author) {
            $this->assertSame($country, $author->business_address->city->country->name);
        }

        // There should now be $n * 2 addresses in the DB
        $this->assertSame(2 * $n, AddressFactory::count());
    }

    public function testGetAssociatedFactoryWithMultipleDepthInPlugin()
    {
        $name = 'Foo';
        $path = 'Bills.Customer';
        $article = ArticleFactory::make()->with($path, compact('name'))->persist();

        $this->assertInstanceOf(Customer::class, $article->bills[0]->customer);

        $this->assertSame(1, ArticleFactory::count());
        $this->assertSame(1, CustomerFactory::count());

        $article = ArticleFactory::get($article->id, ['contain' => $path]);
        $this->assertSame($name, $article->bills[0]->customer->name);
    }

    public function testGetAssociatedFactoryInPluginWithNumber()
    {
        $n = 10;
        $article = ArticleFactory::make()->with('Bills', $n)->persist();

        $this->assertInstanceOf(Bill::class, $article->bills[0]);

        $bills = BillFactory::find();
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
            BillFactory::count()
        );

        $this->assertSame(
            $n,
            CustomerFactory::count()
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

        $this->assertSame(2 * $times, AddressFactory::count());
        $country = CountryFactory::get($country->id, ['contain' => 'Cities.Addresses']);

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

        /** @var Article $article */
        $article = ArticleFactory::find()
            ->contain('Authors', function ($q) {
                return $q->order('Authors.name');
            })
            ->first();

        $this->assertSame($name1, $article->authors[0]->name);
        $this->assertSame($name2, $article->authors[1]->name);
    }

    public function testGetAssociatedFactoryWithMultipleDepthAndWithout()
    {
        $author = AuthorFactory::make()
            ->with('BusinessAddress.City.Country')
            ->with('BusinessAddress.City')
            ->without('BusinessAddress')
            ->persist();

        $this->assertNull($author->business_address);
        $this->assertNull(AuthorFactory::get($author->id, ['contain' => 'BusinessAddress'])->business_address);

        // There should be only one address, city and country in the DB
        $this->assertSame(1, AddressFactory::count());
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testSaveMultiplesToOneAssociationShouldSaveOnlyOne()
    {
        $city = CityFactory::make()->with('Country', [
            ['name' => 'Foo1'],
            ['name' => 'Foo2'],
            ['name' => 'Foo3'],
            ['name' => 'Foo4'],
        ])->persist();

        $city = CityFactory::get($city->id, ['contain' => 'Country']);

        $this->assertSame('Foo1', $city->country->name);
        $this->assertSame(1, CountryFactory::count());
        $this->assertSame(1, CityFactory::count());
    }

    public function testAssignWithoutToManyAssociation()
    {
        $countryExpected = 'Foo';
        $countryNotExpected = 'Bar';
        CountryFactory::make(['name' => $countryExpected])
            ->with('Cities', CityFactory::make()
                ->with('Country', ['name' => $countryNotExpected]))
            ->persist();

        $this->assertSame(1, CityFactory::count());
        /** @var City $city */
        $city = CityFactory::find()->contain('Country')->firstOrFail();
        $this->assertSame($countryExpected, $city->country->name);
    }

    /*
     * The created city is associated with a country, which on the
     * fly get $n cities assigned. We make sure that the first city
     * is correctly associated to the country
     */

    public function testAssignWithToManyAssociation()
    {
        $nCities = rand(3, 10);
        $city = CityFactory::make()
            ->with('Country', CountryFactory::make()->with('Cities', $nCities))
            ->persist();

        $citiesAssociatedToCountry = CountryFactory::get($city->country_id, ['contain' => 'Cities'])->cities;

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

        $authorsAssociatedToArticle = AuthorFactory::find()
            ->matching('Articles', function ($q) use ($article) {
                return $q->where(['Articles.id' => $article->id]);
            })
            ->contain('Articles');

        $articlesAssociatedToAuthor = ArticleFactory::find()
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
        $this->assertIsArray($article[$alias]);
        foreach ($article[$alias] as $author) {
            $this->assertInstanceOf(PremiumAuthor::class, $author);
            $this->assertIsInt($author->id);
        }
        $this->assertSame($nPremiumAuthors, AuthorFactory::count());
    }

    public function testCountryWith2CitiesEachOfThemWith2DifferentAddresses()
    {
        $street1 = 'street1';
        $street2 = 'street2';
        $country = CountryFactory::make()->with('Cities[2].Addresses', [
            ['street' => $street1],
            ['street' => $street2],
        ])->persist();

        $country = CountryFactory::get($country->id, ['contain' => 'Cities.Addresses']);

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

        $country = CountryFactory::get($country->id, [
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
        $addresses = AddressFactory::find()
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
        $cities = CityFactory::find()
            ->where(['country_id' => $country->id])
            ->orderAsc('name')
            ->toArray();

        $this->assertSame(2, count($cities));
        $this->assertSame($city1, $cities[0]->name);
        $this->assertSame($city2, $cities[1]->name);
        $this->assertTrue(abs($cities[0]->id - $cities[1]->id) > 1);
        $this->assertSame(2, CityFactory::count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testCountryWith3CitiesMultipleFactories()
    {
        $city1 = 'A city';
        $city2 = 'B city';
        $city3 = 'C city';

        $country = CountryFactory::make()
            ->with('Cities', [
                CityFactory::make([['name' => $city1], ['name' => $city3]])->without('Country'),
                CityFactory::make()->setField('name', $city2)->without('Country'),
            ])
            ->persist();

        // Make sure that all was correctly persisted
        $cities = CityFactory::find()
            ->where(['country_id' => $country->id])
            ->orderAsc('name')
            ->toArray();

        $this->assertSame(3, count($cities));
        $this->assertSame($city1, $cities[0]->name);
        $this->assertSame($city2, $cities[1]->name);
        $this->assertSame($city3, $cities[2]->name);
        $this->assertSame(3, CityFactory::count());
        $this->assertSame(1, CountryFactory::count());
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
        $country = CountryFactory::get($country->id, [
            'contain' => 'Cities',
        ]);

        $this->assertSame(4, count($country->cities));
        $this->assertSame(4, CityFactory::count());

        if (CountryFactory::make()->getTable()->getConnection()->config()['driver'] === Postgres::class) {
            $this->assertSame($city1, CityFactory::get(1)->name);
            $this->assertSame($city2, CityFactory::get(2)->name);
            $this->assertSame($street1, AddressFactory::get(1)->street);
            $this->assertSame($street2, AddressFactory::get(2)->street);
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
        $this->assertIsString($country->cities[0]->name);
        $this->assertSame(false, $country->virtual_cities);

        $country = $factory->persist();
        $this->assertIsString($country->cities[0]->name);
        $this->assertSame(false, $country->virtual_cities);

        // Only the non virtual Cities will be saved
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(1, CountryFactory::count());
    }

    /**
     * Reproduce the issue reported here: https://github.com/vierge-noire/cakephp-fixture-factories/issues/84
     */
    public function testReproduceIssue84()
    {
        $articles = ArticleFactory::make(2)
            ->with('Authors[5]', ['biography' => 'Foo'])
            ->with('Bills')
            ->persist();

        $this->assertSame(2, count($articles));
        foreach ($articles as $article) {
            $this->assertSame(5, count($article->authors));
            foreach ($article->authors as $author) {
                $this->assertSame('Foo', $author->biography);
            }
            $this->assertSame(1, count($article->bills));
        }

        $this->assertSame(2, ArticleFactory::count());
        $this->assertSame(10, AuthorFactory::count());
        $this->assertSame(2, BillFactory::count());
    }

    /**
     * Reproduce the issue reported here: https://github.com/vierge-noire/cakephp-fixture-factories/issues/84
     */
    public function testReproduceIssue84WithArticlesAuthors()
    {
        $articles = ArticleFactory::make(2)
            ->with('ArticlesAuthors[5].Authors', ['biography' => 'Foo'])
            ->with('Bills')
            ->without('Authors') // do not create the default authors
            ->persist();

        $this->assertSame(2, count($articles));
        foreach ($articles as $article) {
            $this->assertSame(5, count($article->articles_authors));
            foreach ($article->articles_authors as $aa) {
                $this->assertSame('Foo', $aa->author->biography);
            }
            $this->assertSame(1, count($article->bills));
        }

        $this->assertSame(2, ArticleFactory::count());
        $this->assertSame(10, AuthorFactory::count());
        $this->assertSame(2, BillFactory::count());
    }

    public function testCompileEntityForToOneAssociation()
    {
        CityFactory::make()->getTable()->belongsTo('Countries');
        $name = 'FooCountry';
        $factories = [
            CityFactory::make()->with('Country', compact('name')),
            CityFactory::make()->with('Countries', compact('name')),
            CityFactory::make()->with('Country')->with('Countries', compact('name')),
            CityFactory::make()->with('Country', ['name' => 'Foo'])->with('Countries', compact('name')),
        ];

        foreach ($factories as $factory) {
            $entity = $factory->getEntity();
            $this->assertSame($name, $entity->country->name);
            $this->assertSame(null, $entity->get('countries'));
        }

        FactoryTableRegistry::getTableLocator()->clear();
        $this->assertSame(false, CityFactory::make()->getTable()->hasAssociation('Countries'));
    }

    public function testDoNotRecreateHasOneAssociationWhenInjectingEntity_OneLevelDepth()
    {
        $city = CityFactory::make()->with('Country')->persist();
        $cityCountryId = $city->country_id;
        $cityCountryName = $city->country->name;

        CityFactory::make($city)->persist();

        $this->assertSame($cityCountryId, $city->country_id);
        $this->assertSame($cityCountryName, $city->country->name);
        $this->assertSame(1, CountryFactory::count());
        $this->assertSame(1, CityFactory::count());
    }

    public function testDoNotRecreateHasOneAssociationWhenInjectingEntity_TwoLevelDepth()
    {
        $city = CityFactory::make()->with('Country')->persist();
        $cityCountryId = $city->country_id;
        $cityCountryName = $city->country->name;

        AddressFactory::make()->with('City', $city)->persist();

        $this->assertSame($cityCountryId, $city->country_id);
        $this->assertSame($cityCountryName, $city->country->name);
        $this->assertSame(1, CountryFactory::count());
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(1, AddressFactory::count());
    }

    public function testDoNotRecreateHasOneAssociationWhenInjectingEntity_ThreeLevelDepth()
    {
        $address = AddressFactory::make()->with('City.Country')->persist();

        AuthorFactory::make()->with('Address', $address)->persist();

        $this->assertSame(1, CountryFactory::count());
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(1, AddressFactory::count());
        $this->assertSame(1, AuthorFactory::count());
    }

    public function testDoNotRecreateHasManyAssociationWhenInjectingEntity_OneLevelDepth()
    {
        $country = CountryFactory::make()->with('Cities')->persist();
        $cityId = $country->cities[0]->id;
        $cityName = $country->cities[0]->name;

        CountryFactory::make($country)->persist();

        $this->assertSame($cityId, $country->cities[0]->id);
        $this->assertSame($cityName, $country->cities[0]->name);
        $this->assertSame(1, CountryFactory::count());
        $this->assertSame(1, CityFactory::count());
    }
}
