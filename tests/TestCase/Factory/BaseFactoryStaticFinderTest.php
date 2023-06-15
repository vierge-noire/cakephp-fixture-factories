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

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

class BaseFactoryStaticFinderTest extends TestCase
{
    use TruncateDirtyTables;

    public $Articles;

    public function setUp(): void
    {
        $this->Articles = TableRegistry::getTableLocator()->get('Articles');
        $this->Articles->getEventManager()->on(
            'Model.beforeFind',
            function(EventInterface $event, SelectQuery $query) {
                return $query->where(['title' => 'Cannot be found.']);
            }
        );
    }

    public function tearDown(): void
    {
        unset($this->Articles);
        TableRegistry::getTableLocator()->clear();
    }

    /**
     * @Given there are $n articles
     * @When I query on the base table, considering the before find in the setup
     * @Then no articles are found
     * @When I query on the factory tables
     * @Then $n articles are found
     */
    public function testBaseFactoryStaticFind()
    {
        $n = 2;
        ArticleFactory::make(2)->unpublished()->persist();
        $this->assertSame([], $this->Articles->find()->toArray());
        $this->assertSame($n, ArticleFactory::find()->count());
        $this->assertSame(0, ArticleFactory::find('published')->count());
        $this->assertSame($n, ArticleFactory::count());
    }

    public function testBaseFactoryStaticFirstOrFail()
    {
        $articles = ArticleFactory::make([
            ['title' => 'title 1'],
            ['title' => 'title 2'],
        ])->persist();

        $firstArticleId = $articles[0]['id'];

        $retrievedArticle = ArticleFactory::firstOrFail(['title' => 'title 1']);
        $this->assertSame($firstArticleId, $retrievedArticle->id);
        $this->assertSame(2, ArticleFactory::count());
    }

    public function testBaseFactoryStaticFirstOrFail_No_Parameters()
    {
        $article = ArticleFactory::make()->persist();

        $retrievedArticle = ArticleFactory::firstOrFail();
        $this->assertSame($article->id, $retrievedArticle->id);
    }

    public function testBaseFactoryStaticFirstOrFailNotFound()
    {
        ArticleFactory::make([
            ['title' => 'title 1'],
            ['title' => 'title 2'],
        ])->persist();

        $this->expectException(RecordNotFoundException::class);
        ArticleFactory::firstOrFail(['title' => 'title 3']);
    }
}
