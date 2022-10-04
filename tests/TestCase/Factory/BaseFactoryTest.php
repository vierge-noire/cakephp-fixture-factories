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

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\CustomerFactory;
use Faker\Generator;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Author;
use TestApp\Model\Entity\City;
use TestApp\Model\Entity\Country;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Model\Table\CountriesTable;
use TestDatabaseCleaner\TruncateDirtyTablesTrait;
use TestPlugin\Model\Entity\Bill;
use function count;
use function is_array;
use function is_int;

class BaseFactoryTest extends TestCase
{
    use TruncateDirtyTablesTrait;

    public function dataForTestConnectionInDataProvider()
    {
        return [
            [AuthorFactory::make()],
            [BillFactory::make()],
        ];
    }

    /**
     * @dataProvider dataForTestConnectionInDataProvider
     * @param BaseFactory $factory
     */
    public function testConnectionInDataProvider(BaseFactory $factory)
    {
        $connectionName = $factory->getTable()->getConnection()->configName();
        $this->assertSame('test', $connectionName);
    }

    public function testGetEntityWithArray()
    {
        $title = 'blah';
        $entity = ArticleFactory::make()->withTitle($title)->getEntity();
        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame($title, $entity->title);
    }

    public function testGetEntityWithCallbackReturningArray()
    {
        $title = 'blah';
        $entity = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) use ($title) {
            return compact('title');
        })->getEntity();

        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame($title, $entity->title);
    }

    public function testGetEntitiesWithArray()
    {
        $n = 3;
        $entities = ArticleFactory::make(['title' => 'blah'], $n)->getEntities();

        $this->assertSame($n, count($entities));
        foreach ($entities as $entity) {
            $this->assertInstanceOf(EntityInterface::class, $entity);
            $this->assertInstanceOf(Article::class, $entity);
            $this->assertSame('blah', $entity->title);
        }
    }

    public function testGetEntitiesWithCallbackReturningArray()
    {
        $n = 3;
        $entities = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->word,
            ];
        }, $n)->getEntities();

        $this->assertSame($n, count($entities));
        foreach ($entities as $entity) {
            $this->assertInstanceOf(EntityInterface::class, $entity);
            $this->assertInstanceOf(Article::class, $entity);
        }
    }

    public function testGetTable()
    {
        $table = ArticleFactory::make()->getTable();
        $this->assertInstanceOf(ArticlesTable::class, $table);
    }

    /**
     * Given : EntitiesTable has association belongsTo 'EntityType' to table Options
     * When  : Calling EntityFactory withOne OptionFactory
     *         And calling persist
     * Then  : The returned root entity should be of type Entity
     *         And the entity stored in entity_type should be of type Option
     *         And the root entity's foreign key should be an int
     *         And the root entity id key should be an int
     */
    public function testWithOnePersistOneLevel()
    {
        $author = AuthorFactory::make(['name' => 'test author'])
            ->with('Address', AddressFactory::make(['street' => 'test street']))
            ->persist();

        $this->assertSame(true, $author instanceof Author);
        $this->assertSame(true, is_int($author->id));
        $this->assertSame(true, $author->address instanceof Address);
        $this->assertSame($author->address_id, $author->address->id);
    }

    public function testMakeMultipleWithArray()
    {
        $n = 3;
        $entities = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->sentence,
            ];
        }, $n)->persist();

        $this->assertSame($n, count($entities));
        $previousName = '';
        foreach ($entities as $entity) {
            $this->assertSame(true, $entity instanceof Article);
            $this->assertNotSame($previousName, $entity->title);
            $previousName = $entity->title;
        }
    }

    public function testMakeFromArrayMultiple()
    {
        $n = 3;
        $entities = ArticleFactory::make([
            'title' => 'test title',
        ], $n)->persist();

        $this->assertSame($n, count($entities));
        $previousName = '';
        foreach ($entities as $entity) {
            $this->assertSame(true, $entity instanceof Article);
            $previousName = $entity->title;
            $this->assertSame($previousName, $entity->title);
        }
    }

    public function testMakeFromArrayMultipleWithMakeFromArray()
    {
        $n = 3;
        $m = 2;
        $articles = ArticleFactory::make([
            'title' => 'test title',
        ], $n)
            ->withAuthors([
                'name' => 'blah',
            ], $m)
            ->persist();

        $this->assertSame($n, count($articles));

        foreach ($articles as $article) {
            $this->assertInstanceOf(Article::class, $article);
            $this->assertSame(true, is_int($article->id));
            $this->assertSame($m, count($article->authors));
            foreach ($article->authors as $author) {
                $this->assertInstanceOf(Author::class, $author);
                $this->assertSame(true, is_int($author->id));
            }
        }
    }

    public function testMakeFromArrayMultipleWithMakeFromCallable()
    {
        $n = 3;
        $m = 2;
        $articles = ArticleFactory::make([
            'title' => 'test title',
        ], $n)
            ->withAuthors(function (AuthorFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->lastName,
                ];
            }, $m)
            ->persist();

        $this->assertSame($n, count($articles));

        foreach ($articles as $article) {
            $this->assertInstanceOf(Article::class, $article);
            $this->assertSame(true, is_int($article->id));
            $this->assertSame($m, count($article->authors));
            foreach ($article->authors as $author) {
                $this->assertInstanceOf(Author::class, $author);
                $this->assertSame(true, is_int($author->id));
            }
        }
    }

    public function testMakeSingleWithArray()
    {
        $article = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->sentence,
            ];
        })->persist();

        $this->assertSame(true, $article instanceof Article);
    }

    public function testMakeSingleWithArrayWithSubFactory()
    {
        $city = CityFactory::make(function (CityFactory $factory, Generator $faker) {
            $factory->withCountry(function (CountryFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->country,
                ];
            });

            return [
                'name' => $faker->city,
            ];
        })->persist();

        $this->assertSame(true, $city instanceof City);
        $this->assertSame(true, is_int($city->id));
        $this->assertSame(true, $city->country instanceof Country);
        $this->assertSame($city->country_id, $city->country->id);
    }

    public function testMakeMultipleWithArrayWithSubFactory()
    {
        $n = 3;
        $m = 2;
        $articles = ArticleFactory::make(
            function (ArticleFactory $factory, Generator $faker) {
                return ['title' => $faker->sentence];
            },
            $n
        )
        ->withAuthors(
            function (AuthorFactory $factory, Generator $faker) {
                return ['name' => $faker->lastName];
            },
            $m
        )
        ->persist();

        foreach ($articles as $article) {
            $this->assertSame(true, $article instanceof Article);
            $this->assertSame(true, is_int($article->id));
            $this->assertSame($m, count($article->authors));
            foreach ($article->authors as $author) {
                $this->assertInstanceOf(Author::class, $author);
                $this->assertSame(true, is_int($author->id));
            }
        }
    }

    public function testPersistFourlevels()
    {
        $n = 3;
        $m = 2;
        $articles = ArticleFactory::make(['title' => 'test title'], $n)
            ->withAuthors(function (AuthorFactory $factory, Generator $faker) {
                $factory->withAddress(function (AddressFactory $factory, Generator $faker) {
                    $factory->withCity(function (CityFactory $factory, Generator $faker) {
                        $factory->withCountry(function (CountryFactory $factory, Generator $faker) {
                            return [
                                'name' => $faker->country,
                            ];
                        });

                        return [
                            'name' => $faker->city,
                        ];
                    });

                    return [
                        'street' => $faker->streetName,
                    ];
                });

                return [
                    'name' => $faker->lastName,
                ];
            }, $m)
            ->persist();

        $this->assertSame($n, count($articles));

        foreach ($articles as $article) {
            $this->assertInstanceOf(Article::class, $article);
            $this->assertSame($m, count($article->authors));
            foreach ($article->authors as $author) {
                $this->assertInstanceOf(Author::class, $author);
                $this->assertSame(true, is_int($author->id));
                $this->assertSame($author->address_id, $author->address->id);
                $this->assertSame($author->address->city_id, $author->address->city->id);
                $this->assertSame($author->address->city->country_id, $author->address->city->country_id);
            }
        }
    }

    public function testWithOneGetEntityTwoLevel()
    {
        $author = AuthorFactory::make(['name' => 'test author'])
            ->withAddress(function (AddressFactory $factory, Generator $faker) {
                $factory->withCity(function (CityFactory $factory, Generator $faker) {
                    $factory->withCountry(['name' => 'Wonderland']);

                    return [
                        'name' => $faker->city,
                    ];
                });

                return [
                    'street' => $faker->streetName,
                ];
            })
            ->getEntity();

        $this->assertSame(true, $author->address instanceof Address);
        $this->assertSame(true, $author->address->city instanceof City);
        $this->assertSame(true, $author->address->city->country instanceof Country);
    }

    /**
     * Given : The AuthorsTable has an association of type 'belongsTo' to table AddressesTable called 'BusinessAddress'
     * When  : Making an author with a 'business address' key containing a name
     *         And without setting the associated data manually by calling mergeAssociated
     * Then  : When calling persist, the returned entity should be of type Author
     *         And the returned entity should have an id because it was persisted
     *         And the address key should contain an array
     *         And that object should NOT have an id because it was NOT persisted
     */
    public function testMakeHasOneAssociationFromArrayWithoutSettingAssociatedThenPersist()
    {
        $persistedEntity = AuthorFactory::make([
            'name' => 'test author',
            'business_address' => [
                'street' => 'test address',
            ],
        ])->persist();

        $this->assertSame(true, $persistedEntity instanceof Author);
        $this->assertSame(true, is_int($persistedEntity->id));
        $this->assertSame(true, is_array($persistedEntity->business_address));
        $this->assertSame(false, isset($persistedEntity->business_address->id));
    }

    /**
     * Given : The AuthorsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making an author with an 'address' key containing a name
     *         And without setting the associated data manually by calling mergeAssociated
     * Then  : When calling getEntity, the returned entity should be of type Author
     *         And the returned entity should not have an id because it was not persisted
     *         And the address key should contain an array
     *         And that object should NOT have an id because it was NOT persisted
     */
    public function testMakeHasOneAssociationFromArrayWithoutSettingAssociatedThenGetEntity()
    {
        $factory = AuthorFactory::make([
            'name' => 'test project',
            'business_address' => [
                'name' => 'test project address',
            ],
        ]);

        $marshalledEntity = $factory->getEntity();

        $this->assertSame(true, $marshalledEntity instanceof Author);
        $this->assertSame(false, isset($marshalledEntity->id));
        $this->assertSame(true, is_array($marshalledEntity->business_address));
    }

    /**
     * Given : The AuthorsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making an author with an 'address' key containing a name
     *         And setting the associated data manually by calling mergeAssociated
     * Then  : When calling persist,
     *             the returned entity should be of type Author
     *         And the returned entity should have an id because it was persisted
     *         And the address key should contain an Entity of type Address
     *         And that object should have an id because it was persisted
     */
    public function testMakeHasOneAssociationFromArrayWithSettingAssociatedThenPersist()
    {
        $factory = AuthorFactory::make([
            'name' => 'test author',
            'business_address' => [
                'street' => 'test address',
                'city_id' => CityFactory::make()->persist()->id,
            ],
        ])->mergeAssociated(['BusinessAddress']);

        $persistedEntity = $factory->persist();

        $this->assertSame(true, $persistedEntity instanceof Author);
        $this->assertSame(true, is_int($persistedEntity->id));
        $this->assertSame(true, $persistedEntity->business_address instanceof Address);
        $this->assertSame(true, is_int($persistedEntity->business_address->id));
    }

    /**
     * Given : The AuthorsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making a project with an 'address' key containing a name
     *         And setting the associated data manually by calling mergeAssociated
     * Then  : When calling getEntity,
     *             the returned entity should be of type Author
     *         And the returned entity should NOT have an id because it marshalled
     *         And the address key should contain an Entity of type Address
     *         And that object should NOT have an id because it was marshalled
     */
    public function testMakeHasOneAssociationFromArrayWithSettingAssociatedThenGetEntity()
    {
        $factory = AuthorFactory::make([
            'name' => 'test author',
            'business_address' => [
                'street' => 'test street',
            ],
        ])->mergeAssociated(['BusinessAddress']);

        $marshalledEntity = $factory->getEntity();

        $this->assertSame(true, $marshalledEntity instanceof Author);
        $this->assertSame(false, isset($marshalledEntity->id));
        $this->assertSame(true, $marshalledEntity->business_address instanceof Address);
        $this->assertSame(false, isset($marshalledEntity->business_address->id));
    }

    public function testMakeHasOneAssociationFromCallableThenPersist()
    {
        $entity = AuthorFactory::make(function (AuthorFactory $factory, Generator $faker) {
            $factory->withAddress(function (AddressFactory $factory, Generator $faker) {
                return [
                    'street' => $faker->streetAddress,
                ];
            });

            return [
                'name' => $faker->lastName,
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof Author);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->address instanceof Address);
        $this->assertSame(true, is_int($entity->address->id));
    }

    public function testMakeHasOneAssociationFromCallableWithAssociatedDataInSingleArrayThenPersist()
    {
        $author = AuthorFactory::make(function (AuthorFactory $factory, Generator $faker) {
            return [
                'name' => $faker->name,
                'business_address' => [
                    'street' => $faker->streetAddress,
                ],
            ];
        })->persist();

        $this->assertSame(true, $author instanceof Author);
        $this->assertSame(true, is_int($author->id));
        $this->assertSame(false, $author->business_address instanceof Address);
    }

    public function testMakeHasOneAssociationFromCallableWithAssociatedDataUsingWith()
    {
        $entity = AuthorFactory::make(function (AuthorFactory $factory, Generator $faker) {
            $factory->with('Address', AddressFactory::make(['street' => $faker->streetAddress]));

            return ['name' => $faker->lastName];
        })->persist();

        $this->assertSame(true, $entity instanceof Author);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->address instanceof Address);
        $this->assertSame(true, is_int($entity->address->id));
    }

    public function testMakeTenHasOneAssociationFromCallableWithAssociatedDataUsingWith()
    {
        $n = 10;
        $entities = AuthorFactory::make(function (AuthorFactory $factory, Generator $faker) {
            $factory->with('Address', AddressFactory::make(['street' => $faker->streetAddress]));

            return ['name' => $faker->lastName];
        }, $n)->persist();

        $this->assertSame($n, count($entities));
        foreach ($entities as $entity) {
            $this->assertSame(true, $entity instanceof Author);
            $this->assertSame(true, is_int($entity->id));
            $this->assertSame(true, $entity->address instanceof Address);
            $this->assertSame(true, is_int($entity->address->id));
        }
    }

    public function testGetEntityAfterMakingMultipleShouldReturnTheFirstOfAll()
    {
        $title = 'Foo';
        $article = ArticleFactory::make(compact('title'), 2)->getEntity();
        $this->assertSame($title, $article->title);
    }

    public function testGetEntityAfterMakingMultipleFromArrayShouldReturnTheFirstOfAll()
    {
        $title = 'Foo';
        $article = ArticleFactory::make([
            ['title' => $title],
            ['title' => 'Bar'],
        ], 2)->getEntity();
        $this->assertSame($title, $article->title);
    }

    public function testGetEntitiesAfterMakingOneShouldNotThrowException()
    {
        $title = 'foo';
        $articles = ArticleFactory::make(compact('title'))->getEntities();
        $this->assertIsArray($articles);
        $this->assertSame($title, $articles[0]->title);
    }

    public function testHasAssociation()
    {
        $authorsTable = AuthorFactory::make()->getTable();
        $this->assertSame(true, $authorsTable->hasAssociation('Address'));
        $this->assertSame(true, $authorsTable->hasAssociation('Articles'));
    }

    public function testAssociationByPropertyName()
    {
        $articlesTable = ArticleFactory::make()->getTable();
        $this->assertSame(true, $articlesTable->hasAssociation(Inflector::camelize('authors')));
    }

    public function testEvaluateCallableThatReturnsArray()
    {
        $callable = function () {
            return [
                'name' => 'blah',
            ];
        };

        $evaluation = $callable();
        $this->assertSame(['name' => 'blah'], $evaluation);
    }

    public function testCreatingFixtureWithPrimaryKey()
    {
        $id = 100;

        $article = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) use ($id) {
            return [
                'id' => $id,
                'name' => $faker->sentence,
             ];
        })->persist();

        $this->assertSame($id, $article->id);
        $this->assertSame(1, ArticleFactory::count());
        $this->assertSame($id, ArticleFactory::find()->firstOrFail()->get('id'));
    }

    public function testPatchingWithAssociationPluginToApp()
    {
        $title = 'Some title';
        $amount = 10;
        $bill = BillFactory::make(compact('amount'))
            ->withArticle(compact('title'))
            ->getEntity();
        $this->assertEquals($title, $bill->article->title);
        $this->assertEquals($amount, $bill->amount);
    }

    public function testPersistingWithAssociationPluginToApp()
    {
        $title = 'Some title';
        $amount = 10;
        $bill = BillFactory::make(compact('amount'))
            ->withArticle(compact('title'))
            ->persist();
        $this->assertSame(true, is_int($bill->id));
        $this->assertSame(true, is_int($bill->article->id));
        $this->assertEquals($title, $bill->article->title);
        $this->assertEquals($amount, $bill->amount);
        $this->assertEquals($bill->article_id, $bill->article->id);
    }

    /**
     * Bills have an article set by default in their factory
     * Therefore the article will not be the same as the
     * articles the Bills belong to. See testPatchingWithAssociationWithDefaultAssociationCorrect
     * for a correct implementation
     */
    public function testPatchingWithAssociationWithDefaultAssociation()
    {
        $title = 'Some title';
        $amount = 10;
        $n = 2;
        $article = ArticleFactory::make(compact('title'))
            ->withBills(compact('amount'), $n)
            ->getEntity();
        $this->assertEquals($title, $article->title);
        $this->assertEquals($n, count($article->bills));
        $this->assertTrue(empty($article->bills[0]->article));
    }

    public function testPersistingWithAssociationWithDefaultAssociationWrong()
    {
        $title = 'Some title';
        $amount = 10;
        $n = 2;
        $article = ArticleFactory::make(compact('title'))
            ->withBills(compact('amount'), $n)
            ->persist();

        $this->assertSame(true, is_int($article->id));
        $this->equalTo($n, count($article->bills));
        $this->assertEquals($title, $article->title);
        foreach ($article->bills as $bill) {
            $this->assertEquals($bill->article_id, $article->id);
            $this->assertTrue(empty($bill->article));
            $this->assertEquals($amount, $bill->amount);
            $this->assertInstanceOf(Bill::class, $bill);
        }
    }

    /**
     * The fixture factories stop infinite propagation
     * Bills have an article set by default in their factory
     * However, this redundant association is stopped
     *
     * @throws \Exception
     */
    public function testPersistingWithAssociationWithDefaultAssociationUnstopped()
    {
        $title = 'Some title';
        $amount = 10;
        $n = 2;
        $article = ArticleFactory::make(compact('title'))
            ->withBillsWithArticle(compact('amount'), $n)
            ->persist();

        $this->assertSame(true, is_int($article->id));
        $this->equalTo($n, count($article->bills));
        $this->assertEquals($title, $article->title);
        foreach ($article->bills as $bill) {
            $this->assertSame($bill->article_id, $article->id);
            $this->assertEquals($amount, $bill->amount);
            $this->assertInstanceOf(Bill::class, $bill);
            $this->assertNull($bill->article);
        }
    }

    public function testPatchingWithAssociationWithinPlugin()
    {
        $name = 'Some name';
        $amount = 10;
        $n = 2;
        $customer = CustomerFactory::make(compact('name'))
            ->withBills(compact('amount'), $n)
            ->getEntity();
        $this->assertEquals($name, $customer->name);
        $this->assertEquals($n, count($customer->bills));
        foreach ($customer->bills as $bill) {
            $this->assertEquals($amount, $bill->amount);
            $this->assertInstanceOf(Bill::class, $bill);
        }
    }

    public function testPersistingWithAssociationWithinPlugin()
    {
        $name = 'Some name';
        $amount = 10;
        $n = 2;
        $customer = CustomerFactory::make(compact('name'))
            ->withBills(compact('amount'), $n)
            ->persist();

        $this->assertSame(true, is_int($customer->id));
        $this->equalTo($n, count($customer->bills));
        $this->assertEquals($name, $customer->name);
        foreach ($customer->bills as $bill) {
            $this->assertEquals($bill->customer_id, $customer->id);
            $this->assertEquals($amount, $bill->amount);
            $this->assertInstanceOf(Bill::class, $bill);
        }
    }

    public function testMultipleWith()
    {
        $firstCity = 'First';
        $secondCity = 'Second';
        $thirdCity = 'Third';
        $address = AddressFactory::make()
            ->withCity(['name' => $firstCity])
            ->withCity(['name' => $secondCity])
            ->withCity(['name' => $thirdCity])
            ->persist();
        $this->assertEquals(1, CityFactory::count());
        $this->assertEquals($thirdCity, $address->city->name);
    }

    public function testWithoutAssociation()
    {
        $article = ArticleFactory::make()->getEntity();
        $this->assertInstanceOf(Author::class, $article->authors[0]);

        $article = ArticleFactory::make()->without('Authors')->getEntity();
        $this->assertNull($article->authors);
    }

    public function testWithoutAssociation2()
    {
        $article = ArticleFactory::make()->withBills()->getEntity();
        $this->assertInstanceOf(Bill::class, $article->bills[0]);

        $article = ArticleFactory::make()->withBills()->without('Bills')->getEntity();
        $this->assertNull($article->bills);
    }

    public function testHandlingOfMultipleIdenticalWith()
    {
        AuthorFactory::make()->withAddress()->withAddress()->persist();

        $this->assertEquals(1, AddressFactory::count());
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
    }

    public function testSaveMultipleHasManyAssociation()
    {
        $amount1 = rand(1, 100);
        $amount2 = rand(1, 100);
        $customer = CustomerFactory::make()
            ->withBills([
                ['amount' => $amount1],
                ['amount' => $amount2],
            ])->persist();

        $this->assertSame(2, BillFactory::count());
        $this->assertEquals($amount1, $customer->bills[0]->amount);
        $this->assertEquals($amount2, $customer->bills[1]->amount);
    }

    public function testSaveMultipleHasManyAssociationAndTimes()
    {
        $times = 2;
        $amount1 = rand(1, 100);
        $amount2 = rand(1, 100);
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
    }

    /**
     * @return array
     */
    public function feedTestSetTimes()
    {
        return [[rand(1, 10)], [rand(1, 10)], [rand(1, 10)], ];
    }

    /**
     * @dataProvider feedTestSetTimes
     * @param int $times
     * @throws \Exception
     */
    public function testSetTimes(int $times)
    {
        ArticleFactory::make()->setTimes($times)->persist();

        $this->assertSame($times, ArticleFactory::count());
    }

    /**
     * The max length of a country name being set in CountriesTable
     * this test verifies that the validation is triggered on regular marshalling/saving
     * , but is ignored by the factories
     */
    public function testSkipValidation()
    {
        $maxLength = CountriesTable::NAME_MAX_LENGTH;
        $CountriesTable = TableRegistry::getTableLocator()->get('Countries');
        $name = str_repeat('a', $maxLength + 1);

        $country = $CountriesTable->newEntity(compact('name'));
        $this->assertTrue($country->hasErrors());
        $this->assertFalse($CountriesTable->save($country));

        $country = CountryFactory::make(compact('name'))->getEntity();
        $this->assertFalse($country->hasErrors());
        $country = CountryFactory::make(compact('name'))->persist();
        $this->assertInstanceOf(Country::class, $country);
    }

    public function testMakeEntityWithNumber()
    {
        $n = 2;
        $country = CountryFactory::make($n)->getEntity();
        $this->assertInstanceOf(Country::class, $country);
    }

    public function testMakeEntitiesWithNumber()
    {
        $n = 2;
        $country = CountryFactory::make($n)->getEntities();
        $this->assertSame($n, count($country));
    }
}
