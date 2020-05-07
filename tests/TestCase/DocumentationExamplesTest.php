<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase;


use Faker\Generator;
use PHPUnit\Framework\TestCase;
use CakephpFixtureFactories\Test\Factory\ArticleFactory;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;

class DocumentationExamplesTest extends TestCase
{
    public function testExampleStaticData()
    {
        ArticleFactory::make()->getEntity();
        $articles = ArticleFactory::make(2)->getEntities();
        $previous = '';
        foreach ($articles as $article) {
            $this->assertNotEquals($previous, $article->title);
            $previous = $article->title;
        }

        ArticleFactory::make(['title' => 'Foo'])->getEntity();

        $articles = ArticleFactory::make(['title' => 'Foo'], 3)->getEntities();
        $this->assertEquals(3, count($articles));
        foreach ($articles as $article) {
            $this->assertEquals('Foo', $article->title);
        }

        $articles = ArticleFactory::make(['title' => 'Foo'], 3)->persist();
        $this->assertEquals(3, count($articles));
        foreach ($articles as $article) {
            $this->assertEquals('Foo', $article->title);
        }
    }

    public function testExampleDynamicData()
    {
        $articles = ArticleFactory::make(function(ArticleFactory $factory, Generator $faker) {
            return [
                'title' => $faker->text,
            ];
        }, 3)->persist();
        $this->assertEquals(3, count($articles));
        $previousTitle = 'Foo';
        foreach ($articles as $article) {
            $this->assertNotEquals($previousTitle, $article->title);
            $previousTitle = $article->title;
        }
    }

    public function testExampleChainable()
    {
        $articleFactory = ArticleFactory::make(['title' => 'Foo']);
        $articleFoo = $articleFactory->getEntity();

        $articleJobOffer = $articleFactory->setJobTitle()->getEntity();
        $this->assertEquals('Foo', $articleFoo->title);
        $this->assertNotEquals('Foo', $articleJobOffer->title);
    }

    public function testExampleChainableWithPersist()
    {
        $articleFactory = ArticleFactory::make(['title' => 'Foo']);
        $articleFoo = $articleFactory->persist();

        $articleJobOffer = $articleFactory->setJobTitle()->persist();
        $this->assertEquals('Foo', $articleFoo->title);
        $this->assertNotEquals('Foo', $articleJobOffer->title);
    }

    public function testAssociationsMultiple()
    {
        $article = ArticleFactory::make()->with('authors', AuthorFactory::make(10))->persist();
        $this->assertEquals(10, count($article->authors));
        $previous = '';
        foreach ($article->authors as $author) {
            $this->assertNotEquals($previous, $author->name);
            $previous = $author->name;
        }

        $article = ArticleFactory::make()->withAuthors(10)->persist();
        $this->assertEquals(10, count($article->authors));
        $previous = '';
        foreach ($article->authors as $author) {
            $this->assertNotEquals($previous, $author->name);
            $previous = $author->name;
        }
    }

    public function testAssociationsMultipleWithBiography()
    {
        $article = ArticleFactory::make()->withAuthors(function(AuthorFactory $factory, Generator $faker) {
            return [
                'biography' => $faker->realText()
            ];
        }, 10)->persist();
        $this->assertEquals(10, count($article->authors));
        $lastName = '';
        $lastBio = '';
        foreach ($article->authors as $author) {
            $this->assertNotEquals($lastName, $author->name);
            $lastName = $author->name;
            $this->assertNotEquals($lastBio, $author->biography);
            $lastBio = $author->biography;
        }
    }
}
