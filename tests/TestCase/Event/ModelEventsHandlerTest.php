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

namespace CakephpFixtureFactories\Test\TestCase\Event;

use Cake\Datasource\ModelAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Event\ModelEventsHandler;
use TestApp\Model\Table\ArticlesTable;
use TestApp\Model\Table\CountriesTable;

/**
 * Class ModelEventsHandlerTest
 * @property ArticlesTable $Articles
 * @property CountriesTable $Countries
 */
class ModelEventsHandlerTest extends TestCase
{
    use ModelAwareTrait;

    /**
     * @var CountriesTable
     */
    private $Countries;

    public function setUp()
    {
        $this->loadModel('Articles');
        $this->loadModel('Countries');
    }

    public function tearDown()
    {
        TableRegistry::getTableLocator()->clear();
        unset($this->Articles);
        unset($this->Countries);
        parent::tearDown();
    }

    public function testBeforeMarshalOnTable()
    {
        $country = $this->Countries->newEntity(['name' => 'Foo']);
        $this->assertTrue($country->get('beforeMarshalTriggered'));
    }

    public function testBeforeMarshalOnTableHandled()
    {
        $Countries = TableRegistry::getTableLocator()->get('Countries');
        (new ModelEventsHandler([], []))->handle($Countries);
        $country = $Countries->newEntity(['name' => 'Foo']);
        $this->assertNull($country->get('beforeMarshalTriggered'));
    }
    public function testBeforeMarshalOnTableHandledPermissive()
    {
        $Countries = TableRegistry::getTableLocator()->get('Countries');
        (new ModelEventsHandler(['Model.beforeMarshal'], []))->handle($Countries);
        $country = $Countries->newEntity(['name' => 'Foo']);
        $this->assertTrue($country->get('beforeMarshalTriggered'));
    }

    public function testBeforeSaveInBehaviorOnTable()
    {
        $article = $this->Articles->newEntity(['title' => 'Foo']);
        $this->Articles->saveOrFail($article);
        $this->assertTrue($article->get('beforeSaveInBehaviorTriggered'));
    }

    public function testBeforeSaveInBehaviorOnTableHandled()
    {
        $Articles = TableRegistry::getTableLocator()->get('Articles');
        (new ModelEventsHandler([], []))->handle($Articles);

        $article = $Articles->newEntity(['title' => 'Foo']);
        $Articles->saveOrFail($article);
        $this->assertNull($article->get('beforeSaveInBehaviorTriggered'));
    }

    public function testBeforeSaveInBehaviorOnTableHandledPermissive()
    {
        $Articles = TableRegistry::getTableLocator()->get('Articles');
        (new ModelEventsHandler([], ['Sluggable']))->handle($Articles);

        $article = $Articles->newEntity(['title' => 'Foo']);
        $Articles->saveOrFail($article);
        $this->assertTrue($article->get('beforeSaveInBehaviorTriggered'));
    }
}
