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
namespace CakephpFixtureFactories\Test\TestCase\ORM;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\ORM\FactoryTableRegistry;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use TestApp\Model\Table\AddressesTable;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Model\Table\AuthorsTable;
use TestApp\Model\Table\CitiesTable;
use TestApp\Model\Table\CountriesTable;
use TestPlugin\Model\Table\BillsTable;
use TestPlugin\Model\Table\CustomersTable;

class FactoryTableRegistryTest extends TestCase
{
    public function setUp(): void
    {
        TableRegistry::getTableLocator()->clear();
    }

    public function tables()
    {
        return [
            ['Articles', ArticlesTable::class],
            ['Authors', AuthorsTable::class],
            ['Addresses', AddressesTable::class],
            ['Cities', CitiesTable::class],
            ['Countries', CountriesTable::class],
            ['TestPlugin.Bills', BillsTable::class],
            ['TestPlugin.Customers', CustomersTable::class],
        ];
    }

    /**
     * @dataProvider tables
     */
    public function testReturnedTableShouldHaveSameAssociations(string $tableName, string $table)
    {
        $FactoryTable = FactoryTableRegistry::getTableLocator()->get($tableName);
        $Table = TableRegistry::getTableLocator()->get($tableName);

        $this->assertSame(true, $FactoryTable instanceof $table);
        $this->assertSame(true, $Table instanceof $table);
        $this->assertNotSame(FactoryTableRegistry::getTableLocator(), TableRegistry::getTableLocator());
        $this->assertSame($FactoryTable->getEntityClass(), $Table->getEntityClass());

        $this->assertNotSame($FactoryTable->associations(), $Table->associations());
        foreach ($Table->associations() as $associationName => $association) {
            $this->assertTrue($FactoryTable->hasAssociation($associationName), "Association $associationName not defined on FactoryTable $tableName");
        }

        // EntitiesTable from factory table locator should have a Timestamp behavior.
        $this->assertTrue($FactoryTable->hasBehavior('Timestamp'));
        // EntitiesTable from application table locator should have a Timestamp behavior
        $this->assertTrue($Table->hasBehavior('Timestamp'));
    }

    public function testLoadedPlugin()
    {
        $CountriesTable = TableRegistry::getTableLocator()->get('Countries');

        $expectedPluginsLoaded = [
            'Timestamp',
            'Sluggable',
            'SomePlugin',
        ];

        $this->assertSame($expectedPluginsLoaded, $CountriesTable->behaviors()->loaded());

        $CountryFactory = CountryFactory::make();
        $this->assertSame($expectedPluginsLoaded, $CountryFactory->getTable()->behaviors()->loaded());
    }
}
