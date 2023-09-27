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

use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use CakephpFixtureFactories\Test\Factory\AddressFactory;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\ArticleWithFiveBillsFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use Faker\Generator;
use TestApp\Model\Entity\Author;

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
        $author = AuthorFactory::make()->withArticles($n)->getEntity();
        $this->assertTrue(is_string($author->name));
        foreach ($author->articles as $article) {
            $this->assertTrue(is_string($article->title));
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
     * PatchData should overwrite the data passed
     * in the instantiation
     */
    public function testPatchDataAndCallable()
    {
        $n = 2;
        $title = 'Some title';
        $articles = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->jobTitle,
                'body' => $faker->realText(100),
            ];
        }, $n)->withTitle($title)->persist();
        foreach ($articles as $article) {
            $this->assertEquals($title, $article->title);
        }
    }

    public function testPatchDataAndDefaultValue()
    {
        $title = 'Some title';
        $article = ArticleFactory::make()->patchData(compact('title'))->persist();
        $this->assertSame($title, $article->title);
    }

    public function testPatchDataAndStaticValue()
    {
        $title = 'Some title';
        $article = ArticleFactory::make(['title' => 'Some other title'])->patchData(compact('title'))->persist();
        $this->assertSame($title, $article->title);
    }

    public function testTitleModifiedInMultipleCreationWithCallback()
    {
        $n = 3;
        $articles = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'body' => $faker->realText(100),
            ];
        }, $n)->persist();
        $firstTitle = $articles[0]->title;
        $firstBody = $articles[0]->body;
        unset($articles[0]);
        foreach ($articles as $article) {
            $this->assertNotEquals($firstTitle, $article->title);
            $this->assertNotEquals($firstBody, $article->body);
        }
    }

    public function testDefaultValuesOfArticleDifferent()
    {
        $n = 5;
        $articles = ArticleFactory::make($n)->getEntities();
        $titles = Hash::extract($articles, '{n}.title');
        $this->assertEquals($n, count(array_unique($titles)));
    }

    /**
     * When creating multiples Authors for an article,
     * these authors should be different
     */
    public function testDefaultValuesOfArticleAuthorsDifferent()
    {
        $n = 5;
        $article = ArticleFactory::make()->withAuthors($n)->getEntity();
        $authorNames = Hash::extract($article, 'authors.{n}.name');
        $this->assertEquals($n, count(array_unique($authorNames)));
    }

    public function testDefaultValuesNullGetOriginal()
    {
        $article = ArticleFactory::make()->without('Authors')->getEntity();
        $this->assertNull($article->body);
        $bodyContent = 'body';
        $article->body = $bodyContent;
        $this->assertNull($article->getOriginal('body'));
    }
}
