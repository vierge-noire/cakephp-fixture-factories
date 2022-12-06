<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\ORM\FactoryTableRegistry;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\TableWithoutModelFactory;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Country;
use function count;

class BaseFactoryLoadAssociationsInInitializeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Configure::write('FixtureFactories.testFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('FixtureFactories.testFixtureNamespace');
    }

    public function setUp(): void
    {
        parent::setUp();
        // Clear the association created on the fly
        FactoryTableRegistry::getTableLocator()->clear();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        // Clear the association created on the fly
        FactoryTableRegistry::getTableLocator()->clear();
    }

    public function testLoadAssociationInInitialize_Get_Entity()
    {
        $name = 'Foo';
        $city = CityFactory::make()
            ->with('TableWithoutModel', compact('name'))
            ->getEntity();

        $this->assertSame(1, count($city->get('table_without_model')));
        $tableWithoutModel = $city->get('table_without_model')[0];
        $this->assertInstanceOf(Entity::class, $tableWithoutModel);
        /** @phpstan-ignore-next-line */
        $this->assertSame($name, $tableWithoutModel->name);
    }

    public function testLoadAssociationInInitialize_Get_Entities()
    {
        $name = 'Foo';
        $n = 2;
        $city = CityFactory::make()
            ->with("TableWithoutModel[$n]", compact('name'))
            ->getEntity();

        $this->assertSame($n, count($city->get('table_without_model')));
        foreach ($city->get('table_without_model') as $entity) {
            $this->assertInstanceOf(Entity::class, $entity);
            $this->assertSame($name, $entity->get('name'));
        }
    }

    public function testLoadAssociationInInitialize_Persist()
    {
        $name = 'Foo';
        $city = CityFactory::make()
            ->with('TableWithoutModel', compact('name'))
            ->persist();

        $this->assertSame(1, count($city->get('table_without_model')));
        $tableWithoutModel = $city->get('table_without_model')[0];
        $this->assertInstanceOf(Entity::class, $tableWithoutModel);
        $this->assertSame($name, $tableWithoutModel->get('name'));
        $this->assertSame($city->id, $tableWithoutModel->get('foreign_key'));
        $this->assertSame(1, TableWithoutModelFactory::count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testLoadAssociationOnTheFly_Has_One_Persist()
    {
        $factory = CityFactory::make();
        $factory->getTable()->hasOne('HasOneTableWithoutModel', [
            'className' => 'TableWithoutModel',
            'foreignKey' => 'foreign_key',
        ]);

        $name = 'Foo';
        $city = $factory
            ->with('HasOneTableWithoutModel', compact('name'))
            ->persist();

        $hasOneTableWithoutModel = $city->get('has_one_table_without_model');
        $this->assertInstanceOf(Entity::class, $hasOneTableWithoutModel);
        $this->assertSame($name, $hasOneTableWithoutModel->get('name'));
        $this->assertSame($city->id, $hasOneTableWithoutModel->get('foreign_key'));
        $this->assertSame(1, TableWithoutModelFactory::count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function dataForClassName(): array
    {
        return [['TableWithoutModel'], ['table_without_model']];
    }

    /** @dataProvider dataForClassName */
    public function testLoadAssociationOnTheFly_HasMany_With_Magic_Persist($className)
    {
        CityFactory::make()->getTable()->hasMany('Addresses', compact('className'));

        $name = 'Foo';
        $n = 2;
        $city = CityFactory::make()->with("Addresses[$n]", compact('name'))->persist();

        $addresses = $city->addresses;
        foreach ($addresses as $address) {
            $this->assertInstanceOf(Entity::class, $address);
            $this->assertNotInstanceOf(Address::class, $address);
            $this->assertSame($name, $address->get('name'));
            $this->assertSame($city->id, $address->city_id);
        }
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(0, AddressFactory::count());
        $this->assertSame($n, TableWithoutModelFactory::count());
    }

    /** @dataProvider dataForClassName */
    public function testLoadAssociationOnTheFly_BelongsTo_With_Magic_Persist($className)
    {
        // Because of a foreign key constrain at the DB level, a country with id $city->country_id
        // must be in the DB
        $country = CountryFactory::make()->persist();

        $factory = CityFactory::make();
        $factory->getTable()->belongsTo('Country', compact('className'));

        $name = 'Foo';
        $id = $country->id;
        $city = $factory->with("Country", compact('id', 'name'))->persist();

        $country = $city->country;
        $this->assertInstanceOf(Entity::class, $country);
        $this->assertNotInstanceOf(Country::class, $country);
        $this->assertSame($name, $country->name);
        $this->assertSame(1, CountryFactory::count());
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(1, TableWithoutModelFactory::count());
    }

    /** @dataProvider dataForClassName */
    public function testLoadAssociationOnTheFly_BelongsToMany_With_Magic_Persist($className)
    {
        $factory = AuthorFactory::make();
        $factory->getTable()->belongsToMany('ParallelArticles', [
            'className' => $className,
            'joinTable' => 'articles_authors',
            'targetForeignKey' => 'article_id'
        ]);

        $name = 'Foo';
        $n = 2;
        $author = $factory->with("ParallelArticles[$n]", compact('name'))->persist();

        $articles = $author->get('parallel_articles');
        $this->assertSame($n, count($articles));
        foreach ($articles as $article) {
            $this->assertInstanceOf(Entity::class, $article);
            $this->assertNotInstanceOf(ArticleFactory::class, $article);
            $this->assertSame($name, $article->get('name'));
        }
        $this->assertSame(1, AuthorFactory::count());
        $this->assertSame($n, TableWithoutModelFactory::count());

        $author = AuthorFactory::find()->contain('ParallelArticles')->firstOrFail();
        $articles = $author->get('parallel_articles');
        $this->assertSame($n, count($articles));
        foreach ($articles as $article) {
            $this->assertInstanceOf(Entity::class, $article);
            $this->assertNotInstanceOf(ArticleFactory::class, $article);
            $this->assertSame($name, $article->get('name'));
        }
    }

    public function testLoadAssociationOnTheFly_Overwrite_Existing_Association_Persist()
    {
        $factory = CityFactory::make();
        $this->assertTrue($factory->getTable()->hasAssociation('Addresses'));

        $factory->getTable()->hasMany('Addresses', [
            'className' => 'TableWithoutModel',
            'foreignKey' => 'foreign_key',
        ]);

        $name = 'Foo';
        $n = 2;
        $city = $factory
            ->with("Addresses[$n]", compact('name'))
            ->persist();

        $addresses = $city->addresses;
        foreach ($addresses as $address) {
            $this->assertInstanceOf(Entity::class, $address);
            $this->assertNotInstanceOf(Address::class, $address);
            $this->assertSame($name, $address->get('name'));
            $this->assertSame($city->id, $address->get('foreign_key'));
        }
        $this->assertSame(1, CityFactory::count());
        $this->assertSame(0, AddressFactory::count());
        $this->assertSame($n, TableWithoutModelFactory::count());
    }
}
