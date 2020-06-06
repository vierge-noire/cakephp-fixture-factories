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
namespace CakephpFixtureFactories\Test\TestCase\TestSuite;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Fixture\ArticlesFixture;
use TestApp\Model\Table\ArticlesTable;


class CakeFixturesTest extends TestCase
{
    /**
     * @var ArticlesTable
     */
    public $Articles;

    public $fixtures = [
        ArticlesFixture::class,
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
    }

    /**
     * For the moment, CakeFixtures are simply ignored
     */
    public function testGetArticleFromCakeFixtures()
    {
        $articles = $this->Articles->find();
        $this->assertEquals(1, $articles->count());
    }

    /**
     * Create an Article iwth Factories works
     */
    public function testMakeArticle()
    {
        $n = 10;
        ArticleFactory::make($n)->without('Authors')->persist();
        $articles = $this->Articles->find();
        $this->assertEquals($n + 1, $articles->count());
    }
}
