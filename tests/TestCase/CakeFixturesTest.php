<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;

use App\Test\Fixture\ArticlesFixture;
use Cake\ORM\TableRegistry;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use PHPUnit\Framework\TestCase;
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
        $this->assertEquals(0, $articles->count());
    }

    /**
     * Create an Article iwth Factories works
     */
    public function testMakeArticle()
    {
        ArticleFactory::make()->persist();
        $articles = $this->Articles->find();
        $this->assertEquals(1, $articles->count());
    }
}
