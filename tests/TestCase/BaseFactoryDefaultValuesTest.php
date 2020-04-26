<?php
declare(strict_types=1);

namespace TestFixtureFactories\Test\EntitiesTestCase;


use PHPUnit\Framework\TestCase;
use TestApp\Model\Entity\Author;
use TestFixtureFactories\Test\Factory\AddressFactory;
use TestFixtureFactories\Test\Factory\ArticleWithFiveBillsFactory;
use TestFixtureFactories\Test\Factory\AuthorFactory;

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
        $author = AuthorFactory::make()->withArticles([], $n)->getEntity();
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
}
