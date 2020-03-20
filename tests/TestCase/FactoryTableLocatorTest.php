<?php
declare(strict_types=1);

namespace TestFixtureFactories\Test\TestCase;

use Cake\ORM\TableRegistry;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Table\EntitiesTable;
use TestFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;

class FactoryTableLocatorTest extends TestCase
{
    public function testReturnedTableShouldHaveSameAssociations()
    {
        $factoryEntitiesTable = FactoryTableRegistry::getTableLocator()->get('Entities');
        $entitiesTable = TableRegistry::getTableLocator()->get('Entities');

        $this->assertSame(true, $factoryEntitiesTable instanceof EntitiesTable);
        $this->assertSame(true, $entitiesTable instanceof EntitiesTable);
        $this->assertNotSame(FactoryTableRegistry::getTableLocator(), TableRegistry::getTableLocator());
        $this->assertSame($factoryEntitiesTable->getEntityClass(), $entitiesTable->getEntityClass());

        $this->assertNotSame($factoryEntitiesTable->associations(), $entitiesTable->associations());
        foreach ($entitiesTable->associations() as $association) {
            $this->assertSame(true, $factoryEntitiesTable->hasAssociation($association->getName()));
        }

        // EntitiesTable from factory table locator should not have a Timestamp behavior
        $this->assertSame(false, $factoryEntitiesTable->hasBehavior('Timestamp'));
        // EntitiesTable from application table locator should have a Timestamp behavior
        $this->assertSame(true, $entitiesTable->hasBehavior('Timestamp'));
    }
}