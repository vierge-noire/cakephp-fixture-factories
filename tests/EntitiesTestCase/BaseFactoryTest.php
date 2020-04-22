<?php
declare(strict_types=1);

namespace TestFixtureFactories\Test\EntitiesTestCase;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Author;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Option;
use TestApp\Model\Entity\Project;
use TestApp\Model\Table\TagsTable;
use TestFixtureFactories\Test\Factory\AuthorFactory;
use TestFixtureFactories\Test\Factory\ArticleFactory;
use TestFixtureFactories\Test\Factory\TagFactory;
use TestFixtureFactories\Test\Factory\OptionFactory;
use TestFixtureFactories\Test\Factory\ProjectFactory;
use function count;
use function is_array;
use function is_int;

class BaseFactoryTest extends TestCase
{
    public function testGetEntityWithArray()
    {
        $entity = TagFactory::make(['name' => 'blah'])->getEntity();
        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame('blah', $entity->name);
    }

    public function testGetEntityWithCallbackReturningArray()
    {
        $entity = TagFactory::make(function (TagFactory $factory, Generator $faker){
            return [
                'name' => 'blah'
            ];
        })->getEntity();

        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame('blah', $entity->name);
    }

    public function testGetEntitiesWithArray()
    {
        $entities = TagFactory::make(['name' => 'blah'], 3)->getEntities();

        $this->assertSame(3, count($entities));
        foreach($entities as $entity) {
            $this->assertInstanceOf(EntityInterface::class, $entity);
            $this->assertInstanceOf(Article::class, $entity);
            $this->assertSame('blah', $entity->name);
        }
    }

    public function testGetEntitiesWithCallbackReturningArray()
    {
        $entities = TagFactory::make(function (TagFactory $factory, Generator $faker){
            return [
                'name' => $faker->name
            ];
        }, 3)->getEntities();

        $this->assertSame(3, count($entities));
        foreach($entities as $entity) {
            $this->assertInstanceOf(EntityInterface::class, $entity);
            $this->assertInstanceOf(Article::class, $entity);
        }
    }

    public function testGetTable()
    {
        $table = TagFactory::make()->getTable();
        $this->assertInstanceOf(TagsTable::class, $table);
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
        $entity = TagFactory::make(['name' => 'test entity'])
            ->with('EntityType', OptionFactory::make(['name' => 'test entity type']))
            ->persist();

        $this->assertSame(true, $entity instanceof Article);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->entity_type instanceof Option);
        $this->assertSame(true, is_int($entity->entity_type_id));
    }

    public function testMakeMultipleWithArray()
    {
        $entities = TagFactory::make(function (TagFactory $factory, Generator $faker) {
            return [
                'name' => $faker->name
            ];
        }, 3)->persist();

        $this->assertSame(3, count($entities));
        $previousName = '';
        foreach ($entities as $entity) {
            $this->assertSame(true, $entity instanceof EntityInterface);
            $this->assertNotSame($previousName, $entity->name);
            $previousName = $entity->name;
        }
    }

    public function testMakeFromArrayMultiple()
    {
        $entities = TagFactory::make([
            'name' => 'test name'
        ], 3)->persist();

        $this->assertSame(3, count($entities));
        $previousName = '';
        foreach ($entities as $entity) {
            $this->assertSame(true, $entity instanceof EntityInterface);
            $previousName = $entity->name;
            $this->assertSame($previousName, $entity->name);
        }
    }

    public function testMakeFromArrayMultipleWithMakeFromArray()
    {
        $entities = TagFactory::make([
            'name' => 'test name'
        ], 3)
            ->withAddress([
                'name' => 'blah'
            ])
            ->persist();

        $this->assertSame(3, count($entities));

        foreach ($entities as $entity) {
            $this->assertInstanceOf(Article::class, $entity);
            $this->assertSame(true, is_int($entity->id));
            $this->assertInstanceOf(Address::class, $entity->address);
            $this->assertSame(true, is_int($entity->address->id));
        }
    }

    public function testMakeFromArrayMultipleWithMakeFromCallable()
    {
        $entities = TagFactory::make([
            'name' => 'test name'
        ], 3)
            ->withAddress(function (AuthorFactory $factory, Generator $faker){
                return [
                    'name' => $faker->streetAddress
                ];
            })
            ->persist();

        $this->assertSame(3, count($entities));

        foreach ($entities as $entity) {
            $this->assertInstanceOf(Article::class, $entity);
            $this->assertSame(true, is_int($entity->id));
            $this->assertInstanceOf(Address::class, $entity->address);
            $this->assertSame(true, is_int($entity->address->id));
        }
    }

    public function testMakeSingleWithArray()
    {
        $entity = TagFactory::make(function (TagFactory $factory, Generator $faker) {
            return [
                'name' => $faker->name
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof EntityInterface);
    }

    public function testMakeSingleWithArrayWithSubFactory()
    {
        $entity = TagFactory::make(function (TagFactory $factory, Generator $faker) {
            $factory->withAddress(function (AuthorFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->address
                ];
            });
            return [
                'name' => $faker->name
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->address instanceof EntityInterface);
        $this->assertSame(true, is_int($entity->address->id));
    }

    public function testMakeMultipleWithArrayWithSubFactory()
    {
        $entities = TagFactory::make(function (TagFactory $factory, Generator $faker) {
            $factory->withAddress(function (AuthorFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->address
                ];
            });
            return [
                'name' => $faker->name
            ];
        }, 3)->persist();

        foreach($entities as $entity) {
            $this->assertSame(true, $entity instanceof EntityInterface);
            $this->assertSame(true, is_int($entity->id));
            $this->assertSame(true, $entity->address instanceof EntityInterface);
            $this->assertSame(true, is_int($entity->address->id));
        }
    }

    public function testMakeMultipleFromArray()
    {
        $entities = TagFactory::make(['name' => 'test entity'], 10)
            ->withProjects(function (ProjectFactory $factory, Generator $faker) {
                $factory->withAddress(function (AuthorFactory $factory, Generator $faker) {
                    $factory->withCountry(function (ArticleFactory $factory, Generator $faker) {
                        return [
                            'name' => $faker->country
                        ];
                    });
                    return [
                        'name' => $faker->streetAddress
                    ];
                });
                return ['name' => $faker->colorName];
            }, 3)
            ->withAddress(function (AuthorFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->address
                ];
            })
            ->persist();

        $this->assertSame(10, count($entities));

        foreach ($entities as $entity) {
            $this->assertInstanceOf(Article::class, $entity);
            $this->assertSame(3, count($entity->project));
            $this->assertInstanceOf(Project::class, $entity->project[0]);
        }
    }

    public function testWithOnePersistTwoLevel()
    {
        $entity = TagFactory::make(['name' => 'test entity'])
            ->withProjects(function (ProjectFactory $factory, Generator $faker) {
                $factory->withAddress(['name' => $faker->address]);
                return [
                    'name' => $faker->name
                ];
            }, 3)
            ->withAddress(function (AuthorFactory $factory, Generator $faker) {
                // data for CountryFactory
                $factory->withCountry(function (ArticleFactory $factory, Generator $faker) {
                    $factory->withContinent(['name' => 'Europe']);
                    return [
                        'name' => $faker->country
                    ];
                });
                // data for AddressFactory
                return [
                    'name' => $faker->address
                ];
            })
            ->persist();

        $this->assertSame(true, $entity->address instanceof Address);
        $this->assertSame(true, is_int($entity->address_id));
        $this->assertSame(true, $entity->address->country instanceof Author);
        $this->assertSame(true, is_int($entity->address->country_id));
        $this->assertSame(true, $entity->address->country->continent instanceof Option);
        $this->assertSame(true, is_int($entity->address->country->continent_id));
    }


    public function testWithOnePersistTwoLevelTenTimes()
    {
        $entities = TagFactory::make(['name' => 'test entity'], 10)
            ->withProjects(function (ProjectFactory $factory, Generator $faker) {
                $factory->withAddress(['name' => $faker->address]);
                return [
                    'name' => $faker->name
                ];
            }, 3)
            ->withAddress(function (AuthorFactory $factory, Generator $faker) {
                // data for CountryFactory
                $factory->withCountry(function (ArticleFactory $factory, Generator $faker) {
                    $factory->withContinent(['name' => 'Europe']);
                    return [
                        'name' => $faker->country
                    ];
                });

                // data for AddressFactory
                return [
                    'name' => $faker->address
                ];
            })
            ->persist();

        $this->assertSame(10, count($entities));

        foreach ($entities as $entity) {
            $this->assertSame(3, count($entity->project));
            $this->assertSame(true, $entity->address instanceof Address);
            $this->assertSame(true, is_int($entity->address_id));
            $this->assertSame(true, $entity->address->country instanceof Author);
            $this->assertSame(true, is_int($entity->address->country_id));
            $this->assertSame(true, $entity->address->country->continent instanceof Option);
            $this->assertSame(true, is_int($entity->address->country->continent_id));
        }
    }

    public function testWithOneGetEntityTwoLevel()
    {
        $entity = TagFactory::make(['name' => 'test entity'])
            ->withProjects(function (ProjectFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->name
                ];
            }, 3)
            ->withAddress(function (AuthorFactory $factory, Generator $faker) {
                // data for CountryFactory
                $factory->withCountry(function (ArticleFactory $factory, Generator $faker) {
                    $factory->withContinent(['name' => 'Europe']);
                    return [
                        'name' => $faker->country
                    ];
                });

                // data for AddressFactory
                return [
                    'name' => $faker->address
                ];
            })
            ->getEntity();

        //debug($entity);

        $this->assertSame(true, $entity->address instanceof Address);
        //$this->assertSame(true, is_int($entity->address_id));
        $this->assertSame(true, $entity->address->country instanceof Author);
        //$this->assertSame(true, is_int($entity->address->country_id));
        $this->assertSame(true, $entity->address->country->continent instanceof Option);
        //$this->assertSame(true, is_int($entity->address->country->continent_id));
    }


    /**
     * Given : The ProjectsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making a project with an 'address' key containing a name
     *         And without setting the associated data manually by calling mergeAssociated
     * Then  : When calling persist, the returned entity should be of type Entity\Project
     *         And the returned entity should have an id because it was persisted
     *         And the address key should contain an array
     *         And that object should NOT have an id because it was NOT persisted
     */
    public function testMakeHasOneAssociationFromArrayWithoutSettingAssociatedThenPersist()
    {
        $factory = ProjectFactory::make([
            'name' => 'test project',
            'address' => [
                'name' => 'test project address'
            ]
        ]);

        $persistedEntity = $factory->persist();

        $this->assertSame(true, $persistedEntity instanceof Project);
        $this->assertSame(true, is_int($persistedEntity->id));
        $this->assertSame(true, is_array($persistedEntity->address));
        $this->assertSame(false, isset($persistedEntity->address->id));
    }

    /**
     * Given : The ProjectsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making a project with an 'address' key containing a name
     *         And without setting the associated data manually by calling mergeAssociated
     * Then  : When calling getEntity, the returned entity should be of type Entity\Project
     *         And the returned entity should have an id because it was persisted
     *         And the address key should contain an array
     *         And that object should NOT have an id because it was NOT persisted
     */
    public function testMakeHasOneAssociationFromArrayWithoutSettingAssociatedThenGetEntity()
    {
        $factory = ProjectFactory::make([
            'name' => 'test project',
            'address' => [
                'name' => 'test project address'
            ]
        ]);

        $marshalledEntity = $factory->getEntity();

        $this->assertSame(true, $marshalledEntity instanceof Project);
        $this->assertSame(false, isset($marshalledEntity->id));
        $this->assertSame(true, is_array($marshalledEntity->address));
    }

    /**
     * Given : The ProjectsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making a project with an 'address' key containing a name
     *         And setting the associated data manually by calling mergeAssociated
     * Then  : When calling persist,
     *             the returned entity should be of type Entity\Project
     *         And the returned entity should have an id because it was persisted
     *         And the address key should contain an Entity of type Entity\Address
     *         And that object should have an id because it was persisted
     */
    public function testMakeHasOneAssociationFromArrayWithSettingAssociatedThenPersist()
    {
        $factory = ProjectFactory::make([
            'name' => 'test project',
            'address' => [
                'name' => 'test project address'
            ]
        ])->mergeAssociated(['Address']);

        $persistedEntity = $factory->persist();

        $this->assertSame(true, $persistedEntity instanceof Project);
        $this->assertSame(true, is_int($persistedEntity->id));
        $this->assertSame(true, $persistedEntity->address instanceof Address);
        $this->assertSame(true, is_int($persistedEntity->address->id));
    }

    /**
     * Given : The ProjectsTable has an association of type 'hasOne' to table AddressesTable called 'Address'
     * When  : Making a project with an 'address' key containing a name
     *         And setting the associated data manually by calling mergeAssociated
     * Then  : When calling getEntity,
     *             the returned entity should be of type Entity\Project
     *         And the returned entity should NOT have an id because it marshalled
     *         And the address key should contain an Entity of type Entity\Address
     *         And that object should NOT have an id because it was marshalled
     */
    public function testMakeHasOneAssociationFromArrayWithSettingAssociatedThenGetEntity()
    {
        $factory = ProjectFactory::make([
            'name' => 'test project',
            'address' => [
                'name' => 'test project address'
            ]
        ])->mergeAssociated(['Address']);

        $marshalledEntity = $factory->getEntity();

        $this->assertSame(true, $marshalledEntity instanceof Project);
        $this->assertSame(false, isset($marshalledEntity->id));
        $this->assertSame(true, $marshalledEntity->address instanceof Address);
        $this->assertSame(false, isset($marshalledEntity->address->id));
    }

    public function testMakeHasOneAssociationFromCallableThenPersist()
    {
        $entity = ProjectFactory::make(function (ProjectFactory $factory, Generator $faker) {
            $factory->withAddress(function (AuthorFactory $factory, Generator $faker) {
                return [
                    'name' => $faker->streetAddress
                ];
            });
            return [
                'name' => $faker->name,
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof Project);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->address instanceof Address);
        $this->assertSame(true, is_int($entity->address->id));
    }

    public function testMakeHasOneAssociationFromCallableWithAssociatedDataInSingleArrayThenPersist()
    {
        $entity = ProjectFactory::make(function (ProjectFactory $factory, Generator $faker) {
            return [
                'name' => $faker->name,
                'address' => [
                    'name' => $faker->streetAddress
                ]
            ];
        })->persist();

        $this->assertSame(true, $entity instanceof Project);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(false, $entity->address instanceof Address);
    }

    public function testMakeHasOneAssociationFromCallableWithAssociatedDataUsingWith()
    {
        $entity = ProjectFactory::make(function (ProjectFactory $factory, Generator $faker) {
            $factory->with('Address', AuthorFactory::make(['name' => $faker->streetAddress]));
            return ['name' => $faker->name];
        })->persist();

        $this->assertSame(true, $entity instanceof Project);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->address instanceof Address);
        $this->assertSame(true, is_int($entity->address->id));
    }

    public function testMakeTenHasOneAssociationFromCallableWithAssociatedDataUsingWith()
    {
        $entities = ProjectFactory::make(function (ProjectFactory $factory, Generator $faker) {
            $factory->with('Address', AuthorFactory::make(['name' => $faker->streetAddress]));
            return ['name' => $faker->name];
        }, 10)->persist();

        $this->assertSame(10, count($entities));
        foreach ($entities as $entity) {
            $this->assertSame(true, $entity instanceof Project);
            $this->assertSame(true, is_int($entity->id));
            $this->assertSame(true, $entity->address instanceof Address);
            $this->assertSame(true, is_int($entity->address->id));
        }
    }

    public function testGetEntityAfterMakingMultipleShouldThrowException()
    {
        $this->expectException(RuntimeException::class);
        TagFactory::make(['name' => 'blah'], 2)->getEntity();
    }

    public function testGetEntitiesAfterMakingOneShouldThrowException()
    {
        $this->expectException(RuntimeException::class);
        TagFactory::make(['name' => 'blah'], 1)->getEntities();
    }

    public function testHasAssociation()
    {
        $entitiesTable = TagFactory::make()->getTable();
        $this->assertSame(true, $entitiesTable->hasAssociation('address'));
        $this->assertSame(true, $entitiesTable->hasAssociation('Address'));
        $this->assertSame(true, $entitiesTable->hasAssociation('entitytype'));
        $this->assertSame(true, $entitiesTable->hasAssociation('EntityType'));
    }

    public function testAssociationByPropertyName()
    {
        $entitiesTable = TagFactory::make()->getTable();
        $this->assertSame(true, $entitiesTable->hasAssociation(Inflector::camelize('entity_type')));
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
        $entity = TagFactory::make(function (TagFactory $factory, Generator $faker){
            return [
                'id' => 100,
                'name' => $faker->name
             ];
        })->persist();

        $entitiesTable = TableRegistry::getTableLocator()->get('Entities');
        $entities = $entitiesTable->find()->toArray();
//        dd(
//            $entities
//        );

        $this->assertSame(100, $entity->id);
        $this->assertSame(1, count($entities));
        $this->assertSame(100, $entities[0]->id);
    }
}
