<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Fixture\ArticlesFixture;
use TestApp\Model\Table\ArticlesTable;


class CakeFixturesWithoutAutoFixturesTest extends TestCase
{
    /**
     * @var ArticlesTable
     */
    public $Articles;

    public $fixtures = [
        ArticlesFixture::class,
    ];

    public $autoFixtures = false;

    public function setUp()
    {
        parent::setUp();

        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
    }

    public function testGetArticleFromCakeFixturesWithoutLoading()
    {
        $articles = $this->Articles->find();
        $this->assertEquals(0, $articles->count());
    }

    /**
     * For the moment, CakeFixtures are simply ignored
     */
    public function testGetArticleFromCakeFixtures()
    {
        $this->loadFixtures('Articles');
        $articles = $this->Articles->find();
        $this->assertEquals(1, $articles->count());
    }

    /**
     * Create an Article iwth Factories works
     */
    public function testMakeArticle()
    {
        $this->loadFixtures('Articles');
        $n = 10;
        ArticleFactory::make($n)->persist();
        $articles = $this->Articles->find();
        $this->assertEquals($n + 1, $articles->count());
    }
}
