<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Test\TestCase\Factory;

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Factory\FactoryAwareTrait;
use CakephpTestSuiteLight\SkipTablesTruncation;

class FactoryAwareTraitUnitTest extends TestCase
{
    use FactoryAwareTrait;
    use SkipTablesTruncation;

    public function getFactoryNamespaceData(): array
    {
        return [
            [null, 'TestApp\Test\Factory'],
            ['FooPlugin', 'FooPlugin\Test\Factory'],
            ['FooCorp/BarPlugin', 'FooCorp\BarPlugin\Test\Factory'],
        ];
    }

    /** @dataProvider getFactoryNamespaceData */
    public function testGetFactoryNamespace(?string $plugin, string $expected)
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
    public function testGetFactoryClassName(string $name, string $expected)
    {
        $trait = $this->getObjectForTrait(FactoryAwareTrait::class);
        $this->assertEquals($expected, $trait->getFactoryClassName($name));
    }

    public function getFactoryNameData(): array
    {
        return [
            ['Apples', 'AppleFactory', 'AppleFactory.php'],
            ['apples', 'AppleFactory', 'AppleFactory.php'],
            ['Apple', 'AppleFactory', 'AppleFactory.php'],
            ['apple', 'AppleFactory', 'AppleFactory.php'],
            ['pineApples', 'PineAppleFactory', 'PineAppleFactory.php'],
            ['PineApples', 'PineAppleFactory', 'PineAppleFactory.php'],
            ['pine_apples', 'PineAppleFactory', 'PineAppleFactory.php'],
            ['pine_apple', 'PineAppleFactory', 'PineAppleFactory.php'],
            ['Fruits/PineApple', 'Fruits\\PineAppleFactory', 'Fruits' . DIRECTORY_SEPARATOR . 'PineAppleFactory.php'],
            ['Food/Fruits/PineApple', 'Food\\Fruits\\PineAppleFactory', 'Food' . DIRECTORY_SEPARATOR . 'Fruits' . DIRECTORY_SEPARATOR . 'PineAppleFactory.php'],
        ];
    }

    /** @dataProvider getFactoryNameData */
    public function testGetFactoryNameFromModelName(string $name, string $factoryName, string $factoryFileName)
    {
        $this->assertEquals($factoryName, $this->getFactoryNameFromModelName($name));
        $this->assertEquals($factoryFileName, $this->getFactoryFileName($name));
    }
}
