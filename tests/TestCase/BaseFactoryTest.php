<?php

namespace TestFixtureFactories\Test\TestCase;

use Cake\Datasource\EntityInterface;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Entity\Entity;
use TestApp\Model\Entity\Option;
use TestApp\Model\Table\EntitiesTable;
use TestFixtureFactories\Test\Factory\EntityFactory;
use TestFixtureFactories\Test\Factory\OptionFactory;
use function is_int;

class BaseFactoryTest extends TestCase
{
    public function testGetEntity()
    {
        $entity = EntityFactory::make(['name' => 'blah'])->getEntity();
        $this->assertSame(true, $entity instanceof EntityInterface);
        $this->assertSame(true, $entity instanceof Entity);
    }

    public function testGetTable()
    {
        $table = EntityFactory::make()->getTable();
        $this->assertSame(true, $table instanceof EntitiesTable);
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
        $entity = EntityFactory::make(['name' => 'test entity'])
            ->withOne('entity_type', OptionFactory::make(['name' => 'test entity type']))
            ->persist();

        $this->assertSame(true, $entity instanceof Entity);
        $this->assertSame(true, is_int($entity->id));
        $this->assertSame(true, $entity->entity_type instanceof Option);
        $this->assertSame(true, is_int($entity->entity_type_id));
    }
}
