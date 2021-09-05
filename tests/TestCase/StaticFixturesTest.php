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
namespace CakephpFixtureFactories\Test\TestCase;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Fixture\ArticlesFixture;

class StaticFixturesTest extends TestCase
{
    public $fixtures = [
        ArticlesFixture::class
    ];

    public function testLoadStaticFixtures()
    {
        $article = ArticleFactory::find()->firstOrFail();
        $this->assertInstanceOf(Entity::class, $article);
    }
}
