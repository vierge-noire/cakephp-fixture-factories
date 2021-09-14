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

use Cake\ORM\Query;
use Cake\TestSuite\Fixture\FixtureStrategyInterface;
use Cake\TestSuite\Fixture\TransactionStrategy;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use CakephpFixtureFactories\Test\Fixture\AddressesFixture;
use CakephpFixtureFactories\Test\Fixture\ArticlesAuthorsFixture;
use CakephpFixtureFactories\Test\Fixture\ArticlesFixture;
use CakephpFixtureFactories\Test\Fixture\AuthorsFixture;
use CakephpFixtureFactories\Test\Fixture\CitiesFixture;
use CakephpFixtureFactories\Test\Fixture\CountriesFixture;
use TestApp\Model\Entity\Article;
use TestApp\Model\Entity\Author;

class StaticFixturesTest extends TestCase
{
    protected $fixtures = [
        AddressesFixture::class,
        ArticlesFixture::class,
        ArticlesAuthorsFixture::class,
        AuthorsFixture::class,
        CitiesFixture::class,
        CountriesFixture::class,
    ];

    protected function getFixtureStrategy(): FixtureStrategyInterface
    {
        return new TransactionStrategy();
    }

    public function testLoadStaticFixtures_Articles()
    {
        $article = ArticleFactory::find()->firstOrFail();
        $this->assertInstanceOf(Article::class, $article);
    }

    public function testLoadStaticFixtures_Authors()
    {
        $author = AuthorFactory::find()->firstOrFail();
        $this->assertInstanceOf(Author::class, $author);
    }

    public function testLoadStaticFixtures_FindAustralianAuthors()
    {
        $australianAuthors = AuthorFactory::find()->innerJoinWith('Address.City.Country', function(Query $q) {
            return $q->where(['Country.name' => 'Australia']);
        });

        $this->assertSame(1, $australianAuthors->count());
        $this->assertSame(2, $australianAuthors->firstOrFail()->id);
    }

    public function testLoadStaticFixtures_FindAustralianArticles()
    {
        $australianArticles = ArticleFactory::find()->innerJoinWith('Authors.Address.City.Country', function(Query $q) {
            return $q->where(['Country.name' => 'Australia']);
        });

        $this->assertSame(1, $australianArticles->count());
        $this->assertSame(2, $australianArticles->firstOrFail()->id);
    }
}
