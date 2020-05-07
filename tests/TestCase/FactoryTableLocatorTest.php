<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Entity\Country;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Model\Table\CountriesTable;
use CakephpFixtureFactories\ORM\TableRegistry\FactoryTableRegistry;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;

class FactoryTableLocatorTest extends TestCase
{
    public function testReturnedTableShouldHaveSameAssociations()
    {
        $factoryArticlesTable = FactoryTableRegistry::getTableLocator()->get('Articles');
        $articlesTable = TableRegistry::getTableLocator()->get('Articles');

        $this->assertSame(true, $factoryArticlesTable instanceof ArticlesTable);
        $this->assertSame(true, $articlesTable instanceof ArticlesTable);
        $this->assertNotSame(FactoryTableRegistry::getTableLocator(), TableRegistry::getTableLocator());
        $this->assertSame($factoryArticlesTable->getEntityClass(), $articlesTable->getEntityClass());

        $this->assertNotSame($factoryArticlesTable->associations(), $articlesTable->associations());
        foreach ($articlesTable->associations() as $association) {
            $this->assertSame(true, $factoryArticlesTable->hasAssociation($association->getName()));
        }

        // EntitiesTable from factory table locator should not have a Timestamp behavior. This is the only behavior that is allowed
        $this->assertSame(true, $factoryArticlesTable->hasBehavior('Timestamp'));
        // EntitiesTable from application table locator should have a Timestamp behavior
        $this->assertSame(true, $articlesTable->hasBehavior('Timestamp'));
    }


    /**
     * The max length of a country name being set in CountriesTable
     * this test verifies that the validation is triggered on regular marschalling/saving
     * , but is ignored by the factories
     */
    public function testSkipValidation()
    {
        $maxLength = CountriesTable::NAME_MAX_LENGTH;
        $validator = new Validator();
        $validator->maxLength('name', $maxLength);

        $name = str_repeat('a', $maxLength + 1);

        $countriesTable = TableRegistry::getTableLocator()->get('Countries');

        $country = $countriesTable->newEntity(compact('name'));
        $this->assertTrue($country->hasErrors());
        $this->assertFalse($countriesTable->save($country));

        $country = CountryFactory::make(compact('name'))->getEntity();
        $this->assertFalse($country->hasErrors());
        $country = CountryFactory::make(compact('name'))->persist();
        $this->assertInstanceOf(Country::class, $country);
    }

    public function testIgnoreBeforeSave()
    {
        $name = 'Wonderland';
        $forcedName = 'Notwonderland';
        $countriesTable = TableRegistry::getTableLocator()->get('Countries');

        $countriesTable->getEventManager()->on('Model.beforeSave', function (Event $event, EntityInterface $entity) use ($forcedName) {
            $entity->name = $forcedName;
        });

        $country = $countriesTable->newEntity(compact('name'));
        $countriesTable->save($country);

        $this->assertEquals($forcedName, $country->name);

        $country = CountryFactory::make(compact('name'))->persist();
        $this->assertEquals($name, $country->name);
    }

    public function testIgnoreBeforeMarshal()
    {
        $name = 'Wonderland';
        $forcedName = 'Notwonderland';
        $countriesTable = TableRegistry::getTableLocator()->get('Countries');

        $countriesTable->getEventManager()->on('Model.beforeMarshal', function (Event $event, \ArrayObject $entity) use ($forcedName) {
            $entity['name'] = $forcedName;
        });

        $country = $countriesTable->newEntity(compact('name'));
        $this->assertEquals($forcedName, $country->name);

        $country = CountryFactory::make(compact('name'))->getEntity();
        $this->assertEquals($name, $country->name);
    }

    public function testIgnoreBehaviors()
    {
        $articlesTable = TableRegistry::getTableLocator()->get('Articles');
        $articlesTable->addBehavior('Sluggable');

        $title = "This Article";
        $article = $articlesTable->newEntity(compact('title'));
        $articlesTable->save($article);

        $this->assertEquals('This-Article', $article->slug);

        $article = ArticleFactory::make(compact('title'))->persist();
        $this->assertNull($article->slug ?? null);
        $this->assertTrue(is_int($article->id));
    }
}