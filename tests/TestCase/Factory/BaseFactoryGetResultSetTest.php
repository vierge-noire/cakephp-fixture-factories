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
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use TestApp\Model\Entity\Address;

class BaseFactoryGetResultSetTest extends TestCase
{
    use TruncateDirtyTables;

    public static function setUpBeforeClass(): void
    {
        Configure::write('FixtureFactories.testFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('FixtureFactories.testFixtureNamespace');
    }

    public static function isPersisted(): array
    {
        return [[false], [true],];
    }

    /**
     * @dataProvider isPersisted
     */
    public function testBaseFactoryGetResultSet(bool $isPersisted)
    {
        $name1 = 'Name 1';
        $name2 = 'Name 2';
        $street = 'Street';
        $factory = ArticleFactory::make([
            ['name' => $name1],
            ['name' => $name2],
        ])
        ->with("Authors.Address", compact('street'));

        $articles = $isPersisted ? $factory->getPersistedResultSet() : $factory->getResultSet();
        $this->assertSame(2, $articles->count());
        $this->assertSame(!$isPersisted, is_null($articles->first()->get('id')));
        $this->assertSame($name1, $articles->first()->get('name'));
        $this->assertSame($street, $articles->first()['authors'][0]['address']['street']);
        $this->assertSame($name2, $articles->last()->get('name'));
        $this->assertSame($street, $articles->last()['authors'][0]['address']['street']);
        $this->assertInstanceOf(Address::class, $articles->first()['authors'][0]['address']);
        $this->assertSame($isPersisted ? 2 : 0, ArticleFactory::count());
    }

    public function testBaseFactoryGetResultSet_With_Ids()
    {
        $id1 = 5;
        $id2 = 10;
        $countries = CountryFactory::make([
            ['id' => $id1],
            ['id' => $id2],
        ])->getResultSet();

        $this->assertSame($id1, $countries->first()->get('id'));
        $this->assertSame($id2, $countries->last()->get('id'));
    }
}
