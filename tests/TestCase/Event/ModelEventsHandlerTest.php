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

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Event\ModelEventsHandler;

/**
 * Class ModelEventsHandlerTest
 */
class ModelEventsHandlerTest extends TestCase
{
    public function tearDown(): void
    {
        parent::tearDown();
        TableRegistry::getTableLocator()->clear();
    }

    public function testBeforeMarshalOnTable()
    {
        $Countries = TableRegistry::getTableLocator()->get('Countries');
        $country = $Countries->newEntity(['name' => 'Foo']);
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
        $Articles = TableRegistry::getTableLocator()->get('Articles');
        $article = $Articles->newEntity(['title' => 'Foo']);
        $Articles->saveOrFail($article);
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
