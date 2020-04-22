<?php
declare(strict_types=1);

namespace TestFixtureFactories\Test\EntitiesTestCase;

use Cake\ORM\TableRegistry;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Table\TagsTable;
use TestFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;

class FactoryTableLocatorTest extends TestCase
{
    public function testReturnedTableShouldHaveSameAssociations()
    {
        $factoryEntitiesTable = FactoryTableRegistry::getTableLocator()->get('Entities');
        $entitiesTable = TableRegistry::getTableLocator()->get('Entities');

        $this->assertSame(true, $factoryEntitiesTable instanceof TagsTable);
        $this->assertSame(true, $entitiesTable instanceof TagsTable);
        $this->assertNotSame(FactoryTableRegistry::getTableLocator(), TableRegistry::getTableLocator());
        $this->assertSame($factoryEntitiesTable->getEntityClass(), $entitiesTable->getEntityClass());

        $this->assertNotSame($factoryEntitiesTable->associations(), $entitiesTable->associations());
        foreach ($entitiesTable->associations() as $association) {
            $this->assertSame(true, $factoryEntitiesTable->hasAssociation($association->getName()));
        }

        // EntitiesTable from factory table locator should not have a Timestamp behavior. This is the only behavior that is allowed
        $this->assertSame(true, $factoryEntitiesTable->hasBehavior('Timestamp'));
        // EntitiesTable from application table locator should have a Timestamp behavior
        $this->assertSame(true, $entitiesTable->hasBehavior('Timestamp'));
    }
}