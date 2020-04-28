<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\EntitiesTestCase;


use Cake\Utility\Hash;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TestApp\Model\Entity\Author;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\ArticleWithFiveBillsFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;

class BaseFactoryDefaultValuesTest extends TestCase
{
    public function testMakeAuthorWithDefaultName()
    {
        $author = AuthorFactory::make()->getEntity();
        $this->assertTrue(is_string($author->name));
        $this->assertTrue(is_string($author->address->street));
        $this->assertTrue(is_string($author->address->city->name));
        $this->assertTrue(is_string($author->address->city->country->name));
   }

    public function testMakeAuthorWithArticlesWithDefaultTitles()
    {
        $n = 2;
        $author = AuthorFactory::make()->withArticles(null, $n)->getEntity();
        $this->assertTrue(is_string($author->name));
        foreach ($author->articles as $article) {
            $this->assertTrue(is_string($article->title));
            $this->assertFalse(isset($article->title->authors));
        }
    }

    public function testPersistAddressWithCityAndCountry()
    {
        $address = AddressFactory::make()->persist();

        $this->assertTrue(is_string($address->street));
        $this->assertTrue(is_string($address->city->name));
        $this->assertTrue(is_string($address->city->country->name));
        $this->assertTrue(is_numeric($address->city_id));
        $this->assertTrue(is_numeric($address->city->country_id));
    }

    public function testChildAssociation()
    {
        $article = ArticleWithFiveBillsFactory::make()->getEntity();

        $this->assertInstanceOf(Author::class, $article->authors[0]);
        $this->equalTo(5, count($article->bills));
    }

    /**
     *
     */
    public function testPatchDataAndCallable()
    {
        $n = 2;
        $title = "Some title";
        $articles = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->jobTitle,
                'content' => $faker->realText()
            ];
        }, $n)->withTitle($title)->persist();
        foreach ($articles as $article) {
            $this->assertEquals($title, $article->title);
        }
    }

    public function testTitleModifiedInMultipleCreationWithCallback()
    {
        $n = 3;
        $articles = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'content' => $faker->realText()
            ];
        }, $n)->persist();
        $firstTitle = $articles[0]->title;
        $firstContent = $articles[0]->content;
        unset($articles[0]);
        foreach ($articles as $article) {
            $this->assertNotEquals($firstTitle, $article->title);
            $this->assertNotEquals($firstContent, $article->content);
        }
    }

    public function testDefautlValuesOfArticleDifferent()
    {
        $n = 5;
        $articles = ArticleFactory::make(null, $n)->toEntities();
        $titles = Hash::extract($articles, '{n}.title');
        $this->assertEquals($n, count(array_unique($titles)));
    }

    /**
     * When creating multiples Authors for an article,
     * these authors should be different
     */
    public function testDefautlValuesOfArticleAuthorsDifferent()
    {
        $n = 5;
        $article = ArticleFactory::make()->withAuthors(null, $n)->getEntity();
        $authorNames = Hash::extract($article, 'authors.{n}.name');
        $this->assertEquals($n, count(array_unique($authorNames)));
    }
}
