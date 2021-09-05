<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FactoryNotFoundException;
use CakephpFixtureFactories\Factory\FactoryAwareTrait;
use CakephpFixtureFactories\Test\Factory\CountryFactory;
use CakephpFixtureFactories\Test\Factory\PremiumAuthorFactory;
use CakephpTestSuiteLight\SkipTablesTruncation;

class FactoryAwareTraitIntegrationTest extends TestCase
{
    use SkipTablesTruncation;

    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function factoryFoundData(): array
    {
        return [
          ['country', CountryFactory::class],
          ['Country', CountryFactory::class],
          ['countries', CountryFactory::class],
          ['Countries', CountryFactory::class],
          ['premiumAuthor', PremiumAuthorFactory::class],
          ['PremiumAuthor', PremiumAuthorFactory::class],
          ['premiumAuthors', PremiumAuthorFactory::class],
          ['PremiumAuthors', PremiumAuthorFactory::class],
        ];
    }

    /** @dataProvider factoryFoundData */
    public function testGetFactoryFound(string $name, string $expected): void
    {
        $trait = $this->getObjectForTrait(FactoryAwareTrait::class);

        $this->assertInstanceOf($expected, $trait->getFactory($name));
    }

    public function testGetFactoryNotFound(): void
    {
        $trait = $this->getObjectForTrait(FactoryAwareTrait::class);

        $this->expectException(FactoryNotFoundException::class);
        $trait->getFactory('Nevermind');
    }

    public function testGetFactoryWithArgs(): void
    {
        $trait = $this->getObjectForTrait(FactoryAwareTrait::class);

        $article = $trait->getFactory('articles', ['title' => 'Foo'])->getEntity();
        $this->assertEquals('Foo', $article->title);

        $articles = $trait->getFactory('articles', 3)->getEntities();
        $this->assertEquals(3, count($articles));

        $articles = $trait->getFactory('articles', ['title' => 'Foo'], 3)->getEntities();
        $this->assertEquals(3, count($articles));
        foreach ($articles as $article) {
            $this->assertEquals('Foo', $article->title);
        }
    }
}
