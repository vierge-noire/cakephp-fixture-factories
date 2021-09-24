<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Factory\FactoryAwareTrait;
use CakephpTestSuiteLight\SkipTablesTruncation;

class FactoryAwareTraitUnitTest extends TestCase
{
    public function getFactoryNamespaceData(): array
    {
        return [
          [null, 'TestApp\Test\Factory'],
          ['FooPlugin', 'FooPlugin\Test\Factory'],
          ['FooCorp/BarPlugin', 'FooCorp\BarPlugin\Test\Factory'],
        ];
    }

    /** @dataProvider getFactoryNamespaceData */
    public function testGetFactoryNamespace(?string $plugin, string $expected): void
    {
        $trait = $this->getObjectForTrait(FactoryAwareTrait::class);
        $this->assertEquals($expected, $trait->getFactoryNamespace($plugin));
    }

    public function getFactoryClassNameData(): array
    {
        return [
          ['Apples', 'TestApp\Test\Factory\AppleFactory'],
          ['FooPlugin.Apples', 'FooPlugin\Test\Factory\AppleFactory'],
        ];
    }

    /** @dataProvider getFactoryClassNameData */
    public function testGetFactoryClassName(string $name, string $expected): void
    {
        $trait = $this->getObjectForTrait(FactoryAwareTrait::class);
        $this->assertEquals($expected, $trait->getFactoryClassName($name));
    }
}
