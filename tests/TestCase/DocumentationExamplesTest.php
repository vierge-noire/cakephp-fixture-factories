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

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;
use Faker\Generator;
use TestApp\Model\Entity\Article;

class DocumentationExamplesTest extends TestCase
{
    public function testArticlesFindPublished()
    {
        $articles = ArticleFactory::make(['published' => 1], 3)->persist();
        ArticleFactory::make(['published' => 0], 2)->persist();

        $result = ArticleFactory::find('published')->find('list')->toArray();

        $expected = [
            $articles[0]->id => $articles[0]->title,
            $articles[1]->id => $articles[1]->title,
            $articles[2]->id => $articles[2]->title,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testExampleStaticData()
    {
        $article = ArticleFactory::make()->getEntity();
        $this->assertInstanceOf(Article::class, $article);

        $articles = ArticleFactory::make(2)->getEntities();
        $previous = '';
        foreach ($articles as $article) {
            $this->assertNotEquals($previous, $article['title']);
            $previous = $article['title'];
        }

        ArticleFactory::make(['title' => 'Foo'])->getEntity();

        $articles = ArticleFactory::make(['title' => 'Foo'], 3)->getEntities();
        $this->assertEquals(3, count($articles));
        foreach ($articles as $article) {
            $this->assertEquals('Foo', $article['title']);
        }

        $articles = ArticleFactory::make(['title' => 'Foo'], 3)->persist();
        $this->assertEquals(3, count($articles));
        foreach ($articles as $article) {
            $this->assertEquals('Foo', $article['title']);
        }
    }

    public function testExampleDynamicData()
    {
        $articles = ArticleFactory::make(function (ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->text(100),
            ];
        }, 3)->persist();
        $this->assertEquals(3, count($articles));
        $previousTitle = 'Foo';
        foreach ($articles as $article) {
            $this->assertNotEquals($previousTitle, $article['title']);
            $previousTitle = $article['title'];
        }
    }

    public function testExampleChainable()
    {
        $articleFactory = ArticleFactory::make(['title' => 'Foo']);
        $articleFoo = $articleFactory->getEntity();

        $articleJobOffer = $articleFactory->setJobTitle()->getEntity();
        $this->assertEquals('Foo', $articleFoo['title']);
        $this->assertNotEquals('Foo', $articleJobOffer['title']);
    }

    public function testExampleChainableWithPersist()
    {
        $articleFactory = ArticleFactory::make(['title' => 'Foo']);
        $articleFoo = $articleFactory->persist();

        $articleJobOffer = $articleFactory->setJobTitle()->persist();
        $this->assertEquals('Foo', $articleFoo['title']);
        $this->assertNotEquals('Foo', $articleJobOffer['title']);
    }

    public function testAssociationsMultiple()
    {
        $article = ArticleFactory::make()->with('Authors', AuthorFactory::make(10))->persist();
        $this->assertEquals(10, count($article['authors']));
        $previous = '';
        foreach ($article['authors'] as $author) {
            $this->assertNotEquals($previous, $author->name);
            $previous = $author->name;
        }

        $article = ArticleFactory::make()->withAuthors(10)->persist();
        $this->assertEquals(10, count($article['authors']));
        $previous = '';
        foreach ($article['authors'] as $author) {
            $this->assertNotEquals($previous, $author->name);
            $previous = $author->name;
        }
    }

    public function testAssociationsMultipleWithBiography()
    {
        $article = ArticleFactory::make()->withAuthors(function (AuthorFactory $factory, Generator $faker) {
            return [
                'biography' => $faker->realText(),
            ];
        }, 10)->persist();
        $this->assertEquals(10, count($article['authors']));
        $lastName = '';
        $lastBio = '';
        foreach ($article['authors'] as $author) {
            $this->assertNotEquals($lastName, $author->name);
            $lastName = $author->name;
            $this->assertNotEquals($lastBio, $author->biography);
            $lastBio = $author->biography;
        }
    }
}
