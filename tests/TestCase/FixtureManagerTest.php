<?php
declare(strict_types=1);

namespace TestFixtureFactories\Test\EntitiesTestCase;


use Cake\ORM\TableRegistry;
use PHPUnit\Framework\TestCase;
use TestFixtureFactories\Test\Factory\AuthorFactory;

class FixtureManagerTest extends TestCase
{
    public function testTablePopulation()
    {
        $testName = 'Test Name';
        AuthorFactory::make(['name' => $testName])->persist();


        $authors = TableRegistry::getTableLocator()
            ->get('Authors')
            ->find();

        $this->assertEquals(1, $authors->count());
        $this->assertEquals(1, $authors->firstOrFail()->id);
    }

    public function testTablesEmptyOnStart()
    {
        $tables = ['addresses', 'articles', 'authors', 'cities', 'countries'];

        foreach ($tables as $table) {
            $Table = TableRegistry::getTableLocator()->get($table);
            $this->assertEquals(0, $Table->find()->count());
        }
    }
}