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
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Factory\EventManager;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\CustomerFactory;
use TestApp\Model\Table\CountriesTable;
use TestPlugin\Model\Behavior\SomePluginBehavior;

class EventManagerTest extends TestCase
{
    /**
     * @var CountriesTable
     */
    private $CountriesTable;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function setUp(): void
    {
        $this->CountriesTable   = TableRegistry::getTableLocator()->get('Countries');

        parent::setUp();
    }

    public function tearDown(): void
    {
        Configure::delete('TestFixtureGlobalBehaviors');
        unset($this->CountriesTable);

        parent::tearDown();
    }

    /**
     * @see EventManager::setDefaultListeningBehaviors()
     */
    public function testSetDefaultListeningBehaviors()
    {
        Configure::write('TestFixtureGlobalBehaviors', ['Sluggable']);

        $factoryMock = $this->createMock(BaseFactory::class);
        $EventManager = new EventManager($factoryMock, 'Foo');

        $this->assertSame(
            ['Sluggable', 'Timestamp'],
            $EventManager->getListeningBehaviors()
        );
    }

    public function testSetBehaviorEmpty()
    {
        $factoryMock = $this->createMock(BaseFactory::class);
        $EventManager = new EventManager($factoryMock, 'Foo');

        $expected = [
            'SomeBehaviorUsedInMultipleTables',
            'Timestamp',
        ];
        $this->assertSame(
            $expected,
            $EventManager->getListeningBehaviors()
        );
    }

    public function provideFactories()
    {
        return [
            [ArticleFactory::make()],
            [AuthorFactory::make()],
            [AddressFactory::make()],
            [CityFactory::make()],
            [CountryFactory::make()],
            [BillFactory::make()],
            [CustomerFactory::make()],
        ];
    }

    /**
     * @dataProvider provideFactories
     * @param BaseFactory $factory
     * @throws \Exception
     */
    public function testTimestamp(BaseFactory $factory)
    {
        $entity = $factory->persist();
        $this->assertNotNull($entity->created);
    }

    public function runSeveralTimesWithOrWithoutEvents()
    {
        return [
            [true], [false], [true], [false],
        ];
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     * @param $applyEvent Bind the event once to the model
     * @throws \Exception
     */
    public function testApplyOrIgnoreBeforeMarshalSetOnTheFly($applyEvent)
    {
        $name = 'Foo';

        $this->CountriesTable->getEventManager()->on('Model.beforeMarshal', function (Event $event, \ArrayObject $entity) use ($applyEvent) {
            $entity['eventApplied'] = $applyEvent;
        });

        // Event should be skipped
        $country = CountryFactory::make()->getEntity();
        $this->assertSame(null, $country->eventApplied);

        $country = CountryFactory::make()->listeningToModelEvents('Model.beforeMarshal')->getEntity();
        $this->assertSame(null, $country->eventApplied);

        // Event should apply
        $country = $this->CountriesTable->newEntity(compact('name'));
        $this->assertSame($applyEvent, $country->eventApplied);

        $country = CountryFactory::makeWithModelEvents()->getEntity();
        $this->assertSame($applyEvent, $country->eventApplied);
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     * @param $applyEvent Bind the event once to the model
     * @throws \Exception
     */
    public function testApplyOrIgnoreBeforeMarshalSetInTable($applyEvent)
    {
        $name = 'Foo';

        // Event should be skipped
        $country = CountryFactory::make()->getEntity();
        $this->assertNull($country->beforeMarshalTriggered);

        // Event should apply
        $country = $this->CountriesTable->newEntity(compact('name'));
        $this->assertTrue($country->beforeMarshalTriggered);

        $country = CountryFactory::makeWithModelEvents()->getEntity();
        $this->assertTrue($country->beforeMarshalTriggered);

        $country = CountryFactory::make()->listeningToModelEvents('Model.beforeMarshal')->getEntity();
        $this->assertTrue($country->beforeMarshalTriggered);
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     * @param $times
     * @throws \Exception
     */
    public function testApplyOrIgnoreEventInBehaviors(bool $times)
    {
        $title = "This Article";
        $slug = "This-Article";

        $article = ArticleFactory::make(compact('title'))->persist();
        $this->assertEquals(null, $article->slug);

        $article = ArticleFactory::makeWithModelEvents(compact('title'))->persist();
        $this->assertEquals($slug, $article->slug);

        $article = ArticleFactory::make(compact('title'))->listeningToBehaviors('Sluggable')->persist();
        $this->assertEquals($slug, $article->slug);
    }

    public function testSetBehaviorOnTheFly()
    {
        $behavior = 'Foo';
        $factoryMock = $this->createMock(BaseFactory::class);
        $EventManager = new EventManager($factoryMock, 'Bar');
        $EventManager->listeningToBehaviors('Foo');

        $expected = [
            'SomeBehaviorUsedInMultipleTables',
            'Timestamp',
            $behavior,
        ];
        $this->assertSame(
            $expected,
            $EventManager->getListeningBehaviors()
        );
    }

    public function testGetEntityOnNonExistentBehavior()
    {
        $this->expectException(\InvalidArgumentException::class);
        $behavior = 'Foo';
        ArticleFactory::make()->listeningToBehaviors($behavior)->getEntity();
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     * @param $times
     * @throws \Exception
     */
    public function testApplyOrIgnoreEventInBehaviorsOnTheFlyWithCountries(bool $times)
    {
        $name = "Some Country";
        $slug = "Some-Country";

        $country = CountryFactory::make(compact('name'))->persist();
        $this->assertNull($country->slug);


        $country = CountryFactory::make(compact('name'))
            ->listeningToBehaviors('Sluggable')
            ->persist();
        $this->assertEquals($slug, $country->slug);
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     * @param $times
     * @throws \Exception
     */
    public function testApplyOrIgnoreEventInPluginBehaviorsOnTheFlyWithCountries(bool $times)
    {
        $field = SomePluginBehavior::BEFORE_SAVE_FIELD;

        $this->CountriesTable->addBehavior('TestPlugin.SomePlugin');

        // The behavior should not apply
        $country = CountryFactory::make()->persist();
        $this->assertNull($country->$field);

        // The behavior should apply
        $country = CountryFactory::make()->listeningToBehaviors('SomePlugin')->persist();
        $this->assertTrue($country->$field);

        // The behavior should not apply
        $country = CountryFactory::make()->persist();
        $this->assertNull($country->$field);

        // The behavior should apply
        Configure::write('TestFixtureGlobalBehaviors', ['SomePlugin']);
        $country = CountryFactory::make()->persist();
        $this->assertTrue($country->$field);
    }
}