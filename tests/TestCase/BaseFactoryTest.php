<?php
declare(strict_types=1);

namespace TestFixtureFactories\Test\EntitiesTestCase;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Author;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\City;
use TestApp\Model\Entity\Country;
use TestApp\Model\Table\ArticlesTable;
use TestFixtureFactories\Test\Factory\AddressFactory;
use TestFixtureFactories\Test\Factory\AuthorFactory;
use TestFixtureFactories\Test\Factory\ArticleFactory;
use TestFixtureFactories\Test\Factory\BillFactory;
use TestFixtureFactories\Test\Factory\CityFactory;
use TestFixtureFactories\Test\Factory\CountryFactory;
use TestFixtureFactories\Test\Factory\CustomerFactory;
use TestPlugin\Model\Entity\Bill;
use function count;
use function is_array;
use function is_int;

class BaseFactoryTest extends TestCase
{
    public function testGetEntityWithArray()
    {
        $entity = ArticleFactory::make(['title' => 'blah'])->getEntity();
        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame('blah', $entity->title);
    }

    public function testGetEntityWithCallbackReturningArray()
    {
        $entity = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker){
            return [
                'title' => 'blah'
            ];
        })->getEntity();

        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame('blah', $entity->title);
    }

    public function testGetEntitiesWithArray()
    {
        $n = 3;
        $entities = ArticleFactory::make(['title' => 'blah'], $n)->getEntities();

        $this->assertSame($n, count($entities));
        foreach($entities as $entity) {
            $this->assertInstanceOf(EntityInterface::class, $entity);
            $this->assertInstanceOf(Article::class, $entity);
            $this->assertSame('blah', $entity->title);
        }
    }

    public function testGetEntitiesWithCallbackReturningArray()
    {
        $n = 3;
        $entities = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker){
            return [
                'title' => $faker->word
            ];
        }, $n)->getEntities();

        $this->assertSame($n, count($entities));
        foreach($entities as $entity) {
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
            ->with('address', AddressFactory::make(['street' => 'test street']))
            ->persist();

        $this->assertSame(true, $author instanceof Author);
        $this->assertSame(true, is_int($author->id));
        $this->assertSame(true, $author->address instanceof Address);
        $this->assertSame($author->id, $author->address->author_id);
    }

    public function testMakeMultipleWithArray()
    {
        $n = 3;
        $entities = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->sentence
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
            'title' => 'test title'
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
            'title' => 'test title'
        ], $n)
            ->withAuthors([
                'name' => 'blah'
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
            'title' => 'test title'
        ], $n)
            ->withAuthors(function (AuthorFactory $factory, Generator $faker){
                return [
                    'name' => $faker->lastName
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
        $entity = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->sentence
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof Article);
    }

    public function testMakeSingleWithArrayWithSubFactory()
    {
        $city = CityFactory::make(function (CityFactory $factory, Generator $faker) {
            $factory->withCountry(function (CountryFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->country
                ];
            });
            return [
                'name' => $faker->city
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
        $articles = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) use ($m) {
            $factory->withAuthors(function (AuthorFactory $factory, Generator $faker) use ($m) {
                return [
                    'name' => $faker->lastName
                ];
            }, $m);
            return [
                'title' => $faker->sentence
            ];
        }, 3)->persist();

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
                                'name' => $faker->country
                            ];
                        });
                        return [
                            'name' => $faker->city
                        ];
                    });
                    return [
                        'street' => $faker->streetName
                    ];
                });
                return [
                    'name' => $faker->lastName
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
                $this->assertSame($author->id, $author->address->author_id);
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
                        'name' => $faker->city
                    ];
                });
                return [
                    'street' => $faker->streetName
                ];
            })
            ->getEntity();

        $this->assertSame(true, $author->address instanceof Address);
        $this->assertSame(true, $author->address->city instanceof City);
        $this->assertSame(true, $author->address->city->country instanceof Country);
    }


    /**
     * Given : The AuthorsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making a project with an 'address' key containing a name
     *         And without setting the associated data manually by calling mergeAssociated
     * Then  : When calling persist, the returned entity should be of type Author
     *         And the returned entity should have an id because it was persisted
     *         And the address key should contain an array
     *         And that object should NOT have an id because it was NOT persisted
     */
    public function testMakeHasOneAssociationFromArrayWithoutSettingAssociatedThenPersist()
    {
        $factory = AuthorFactory::make([
            'name' => 'test author',
            'address' => [
                'street' => 'test address'
            ]
        ]);

        $persistedEntity = $factory->persist();

        $this->assertSame(true, $persistedEntity instanceof Author);
        $this->assertSame(true, is_int($persistedEntity->id));
        $this->assertSame(true, is_array($persistedEntity->address));
        $this->assertSame(false, isset($persistedEntity->address->id));
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
            'address' => [
                'name' => 'test project address'
            ]
        ]);

        $marshalledEntity = $factory->getEntity();

        $this->assertSame(true, $marshalledEntity instanceof Author);
        $this->assertSame(false, isset($marshalledEntity->id));
        $this->assertSame(true, is_array($marshalledEntity->address));
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
            'address' => [
                'street' => 'test address'
            ]
        ])->mergeAssociated(['Address']);

        $persistedEntity = $factory->persist();

        $this->assertSame(true, $persistedEntity instanceof Author);
        $this->assertSame(true, is_int($persistedEntity->id));
        $this->assertSame(true, $persistedEntity->address instanceof Address);
        $this->assertSame(true, is_int($persistedEntity->address->id));
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
            'address' => [
                'street' => 'test street'
            ]
        ])->mergeAssociated(['Address']);

        $marshalledEntity = $factory->getEntity();

        $this->assertSame(true, $marshalledEntity instanceof Author);
        $this->assertSame(false, isset($marshalledEntity->id));
        $this->assertSame(true, $marshalledEntity->address instanceof Address);
        $this->assertSame(false, isset($marshalledEntity->address->id));
    }

    public function testMakeHasOneAssociationFromCallableThenPersist()
    {
        $entity = AuthorFactory::make(function (AuthorFactory $factory, Generator $faker) {
            $factory->withAddress(function (AddressFactory $factory, Generator $faker) {
                return [
                    'street' => $faker->streetAddress
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
        $entity = AuthorFactory::make(function (AuthorFactory $factory, Generator $faker) {
            return [
                'name' => $faker->name,
                'address' => [
                    'street' => $faker->streetAddress
                ]
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof Author);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(false, $entity->address instanceof Address);
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

    public function testGetEntityAfterMakingMultipleShouldThrowException()
    {
        $this->expectException(RuntimeException::class);
        ArticleFactory::make(['name' => 'blah'], 2)->getEntity();
    }

    public function testGetEntitiesAfterMakingOneShouldThrowException()
    {
        $this->expectException(RuntimeException::class);
        ArticleFactory::make(['name' => 'blah'], 1)->getEntities();
    }

    public function testHasAssociation()
    {
        $authorsTable = AuthorFactory::make()->getTable();
        $this->assertSame(true, $authorsTable->hasAssociation('address'));
        $this->assertSame(true, $authorsTable->hasAssociation('Address'));
        $this->assertSame(true, $authorsTable->hasAssociation('Articles'));
        $this->assertSame(true, $authorsTable->hasAssociation('articles'));
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
                'name' => 'blah'
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
                'name' => $faker->sentence
             ];
        })->persist();

        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $articles = $articlesTable->find();

        $this->assertSame($id, $article->id);
        $this->assertSame(1, $articles->count());
        $this->assertSame($id, $articles->firstOrFail()->id);
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

    public function testSavingWithAssociationPluginToApp()
    {
        $title = 'Some title';
        $amount = 10;
        $bill = BillFactory::make(compact('amount'))
            ->withArticle(compact('title'))
            ->persist();
        $this->isTrue(is_int($bill->id));
        $this->isTrue(is_int($bill->article->id));
        $this->assertEquals($title, $bill->article->title);
        $this->assertEquals($amount, $bill->amount);
        $this->assertEquals($bill->article_id, $bill->article->id);
    }

    public function testPatchingWithAssociationAppToPlugin()
    {
        $title = 'Some title';
        $amount = 10;
        $n = 2;
        $article = ArticleFactory::make(compact('title'))
            ->withBills(compact('amount'), $n)
            ->getEntity();
        $this->assertEquals($title, $article->title);
        $this->assertEquals($n, count($article->bills));

    }

    public function testSavingWithAssociationAppToPlugin()
    {
        $title = 'Some title';
        $amount = 10;
        $n = 2;
        $article = ArticleFactory::make(compact('title'))
            ->withBills(compact('amount'), $n)
            ->persist();

        $this->isTrue(is_int($article->id));
        $this->equalTo($n, count($article->bills));
        $this->assertEquals($title, $article->title);
        foreach ($article->bills as $bill) {
            $this->assertEquals($bill->article_id, $article->id);
            $this->assertEquals($amount, $bill->amount);
            $this->assertInstanceOf(Bill::class, $bill);
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

    public function testSavingWithAssociationWithinlugin()
    {
        $name = 'Some name';
        $amount = 10;
        $n = 2;
        $customer = CustomerFactory::make(compact('name'))
            ->withBills(compact('amount'), $n)
            ->persist();

        $this->isTrue(is_int($customer->id));
        $this->equalTo($n, count($customer->bills));
        $this->assertEquals($name, $customer->name);
        foreach ($customer->bills as $bill) {
            $this->assertEquals($bill->customer_id, $customer->id);
            $this->assertEquals($amount, $bill->amount);
            $this->assertInstanceOf(Bill::class, $bill);
        }
    }
}
