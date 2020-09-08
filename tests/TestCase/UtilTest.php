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


use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\TestSuite\SkipTablesTruncation;
use CakephpFixtureFactories\Util;

class UtilTest extends TestCase
{
    use SkipTablesTruncation;

    public function testGetFactoryNameFromModelName()
    {
        $model = 'Apples';
        $this->assertEquals('AppleFactory', Util::getFactoryNameFromModelName($model));
    }

    public function testGetFactoryClassFromModelName()
    {
        $model = 'Apples';
        $this->assertEquals('TestApp\Test\Factory\AppleFactory', Util::getFactoryClassFromModelName($model));
    }

    public function testGetFactoryClassFromModelNameWithinPlugin()
    {
        $model = 'Plugin.Apples';
        $this->assertEquals('Plugin\Test\Factory\AppleFactory', Util::getFactoryClassFromModelName($model));
    }

    public function testGetFactoryNamespace()
    {
        $this->assertEquals(
            'TestApp\Test\Factory',
            Util::getFactoryNamespace()
        );
    }

    public function testGetFactoryNamespaceWithPlugin()
    {
        $plugin = 'Foo';
        $this->assertEquals(
            $plugin . '\Test\Factory',
            Util::getFactoryNamespace($plugin)
        );
    }
}