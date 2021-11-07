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
use CakephpFixtureFactories\Factory\EventCollector;
use CakephpFixtureFactories\ORM\FactoryTableRegistry;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Factory\BillFactory;
use CakephpFixtureFactories\Test\Factory\CityFactory;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\CustomerFactory;
use TestApp\Model\Entity\Address;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\City;
use TestApp\Model\Table\CountriesTable;
use TestPlugin\Model\Behavior\SomePluginBehavior;

class EventCollectorTest extends TestCase
{
    /**
     * @var CountriesTable
     */
    private $Countries;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
        Configure::write('TestFixtureGlobalBehaviors', 'SomeBehaviorUsedInMultipleTables');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
        Configure::delete('TestFixtureGlobalBehaviors');
    }

    public function setUp(): void
    {
        /** @var CountriesTable $Countries */
        $Countries = TableRegistry::getTableLocator()->get('Countries');
        $this->Countries = $Countries;

        parent::setUp();
    }

    public function tearDown(): void
    {
        Configure::delete('TestFixtureGlobalBehaviors');
        unset($this->Countries);

        parent::tearDown();
    }

    /**
     * @see EventCollector::setDefaultListeningBehaviors()
     */
    public function testSetDefaultListeningBehaviors()
    {
        Configure::write('TestFixtureGlobalBehaviors', ['Sluggable']);

        $EventManager = new EventCollector('Foo');

        $this->assertSame(
            ['Sluggable', 'Timestamp'],
            $EventManager->getListeningBehaviors()
        );
    }

    public function testSetBehaviorEmpty()
    {
        $EventManager = new EventCollector('Foo');

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
     */
    public function testTimestamp(BaseFactory $factory)
    {
        $entity = $factory->persist();
        $this->assertNotNull($entity->get('created'));
    }

    public function runSeveralTimesWithOrWithoutEvents()
    {
        return [
            [true], [false], [true], [false],
        ];
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     * @param bool $applyEvent Bind the event once to the model
     */
    public function testApplyOrIgnoreBeforeMarshalSetOnTheFly(bool $applyEvent)
    {
        $name = 'Foo';

        $this->Countries->getEventManager()->on('Model.beforeMarshal', function (Event $event, \ArrayObject $entity) use ($applyEvent) {
            $entity['eventApplied'] = $applyEvent;
        });

        // Event should apply
        $country = $this->Countries->newEntity(compact('name'));
        $this->assertSame($applyEvent, $country->get('eventApplied'));

        $factory = CountryFactory::make();
        $factory->getTable()->getEventManager()->on('Model.beforeMarshal', function (Event $event, \ArrayObject $entity) use ($applyEvent) {
            $entity['eventApplied'] = $applyEvent;
        });
        $country = $factory->getEntity();
        $this->assertSame($applyEvent, $country->get('eventApplied'));
        FactoryTableRegistry::getTableLocator()->clear();

        // Event should be skipped
        $country = CountryFactory::make()->getEntity();
        $this->assertSame(null, $country->get('eventApplied'));

        $country = CountryFactory::make()->listeningToModelEvents('Model.beforeMarshal')->getEntity();
        $this->assertSame(null, $country->get('eventApplied'));
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     */
    public function testApplyOrIgnoreBeforeMarshalSetInTable()
    {
        $name = 'Foo';

        // Event should be skipped
        $country = CountryFactory::make()->getEntity();
        $this->assertNull($country->get('beforeMarshalTriggered'));

        // Event should apply
        $country = $this->Countries->newEntity(compact('name'));
        $this->assertTrue($country->get('beforeMarshalTriggered'));

        $country = CountryFactory::make()->listeningToModelEvents('Model.beforeMarshal')->getEntity();
        $this->assertTrue($country->get('beforeMarshalTriggered'));
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     */
    public function testApplyOrIgnoreEventInBehaviors()
    {
        $title = 'This Article';
        $slug = 'This-Article';

        $article = ArticleFactory::make(compact('title'))->persist();
        $this->assertEquals(null, $article->get('slug'));

        $article = ArticleFactory::make(compact('title'))->listeningToBehaviors('Sluggable')->persist();
        $this->assertEquals($slug, $article->get('slug'));
    }

    public function testSetBehaviorOnTheFly()
    {
        $behavior = 'Foo';
        $EventManager = new EventCollector('Bar');
        $EventManager->listeningToBehaviors(['Foo']);

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
        $behavior = 'Foo';
        $article = ArticleFactory::make()->listeningToBehaviors($behavior)->getEntity();
        $this->assertInstanceOf(Article::class, $article);
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     */
    public function testApplyOrIgnoreEventInBehaviorsOnTheFlyWithCountries()
    {
        $name = 'Some Country';
        $slug = 'Some-Country';

        $country = CountryFactory::make(compact('name'))->persist();
        $this->assertNull($country->get('slug'));

        $country = CountryFactory::make(compact('name'))
            ->listeningToBehaviors('Sluggable')
            ->persist();
        $this->assertEquals($slug, $country->get('slug'));
    }

    /**
     * @dataProvider runSeveralTimesWithOrWithoutEvents
     */
    public function testApplyOrIgnoreEventInPluginBehaviorsOnTheFlyWithCountries()
    {
        $field = SomePluginBehavior::BEFORE_SAVE_FIELD;

        $this->Countries->addBehavior('TestPlugin.SomePlugin');

        // The behavior should not apply
        $country = CountryFactory::make()->persist();
        $this->assertNull($country->get($field));

        // The behavior should apply
        $country = CountryFactory::make()->listeningToBehaviors('SomePlugin')->persist();
        $this->assertTrue($country->get($field));

        // The behavior should not apply
        $country = CountryFactory::make()->persist();
        $this->assertNull($country->get($field));

        // The behavior should apply
        Configure::write('TestFixtureGlobalBehaviors', ['SomePlugin']);
        $country = CountryFactory::make()->persist();
        $this->assertTrue($country->get($field));
    }

    public function testSkipValidation()
    {
        $city = CityFactory::make()->without('Country')->getEntity();
        $this->assertInstanceOf(City::class, $city);
        $this->assertEmpty($city->getErrors());
    }

    public function testSkipValidationInAssociation()
    {
        $address = AddressFactory::make()
            ->with('City', CityFactory::make()->without('Country'))
            ->getEntity();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertInstanceOf(City::class, $address->city);
        $this->assertNull($address->city->country);
        $this->assertEmpty($address->getErrors());
    }

    public function testApplyValidationInAssociation()
    {
        $address = AddressFactory::make()
            ->with(
                'City',
                CityFactory::make()
                    ->listeningToModelEvents('Model.beforeMarshal')
                    ->without('Country')
            )
            ->getEntity();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertInstanceOf(City::class, $address->city);
        $this->assertNull($address->city->country);
        $this->assertTrue($address->city->get('beforeMarshalTriggered'));
    }

    /**
     * Cities have a rule that always return false
     */
    public function testSkipRules()
    {
        $city = CityFactory::make()->persist();
        $this->assertInstanceOf(City::class, $city);
        $this->assertEmpty($city->getErrors());
    }

    public function testSkipRuleInAssociation()
    {
        $address = AddressFactory::make()->getEntity();
        $this->assertInstanceOf(Address::class, $address);
        $this->assertInstanceOf(City::class, $address->city);
        $this->assertEmpty($address->getErrors());
    }

    public function testBeforeMarshalIsTriggeredInAssociationWhenDefinedInDefaultTemplate()
    {
        $bill = BillFactory::make()->getEntity();
        $this->assertTrue($bill->get('beforeMarshalTriggeredPerDefault'));

        $bill = CustomerFactory::make()->withBills()->getEntity()->bills[0];
        $this->assertTrue($bill->get('beforeMarshalTriggeredPerDefault'));
    }

    public function testAfterSaveIsTriggeredInAssociationWhenDefinedInDefaultTemplate()
    {
        $bill = BillFactory::make()->persist();
        $this->assertTrue($bill->get('afterSaveTriggeredPerDefault'));

        $bill = CustomerFactory::make()->withBills()->persist()->bills[0];
        $this->assertTrue($bill->get('afterSaveTriggeredPerDefault'));
    }
}
