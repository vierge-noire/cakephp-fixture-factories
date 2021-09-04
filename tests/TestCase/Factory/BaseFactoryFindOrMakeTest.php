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

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Test\Factory\CountryFactory;

class BaseFactoryFindOrMakeTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Configure::write('TestFixtureNamespace', 'CakephpFixtureFactories\Test\Factory');
    }

    public static function tearDownAfterClass(): void
    {
        Configure::delete('TestFixtureNamespace');
    }

    public function testFindOrMake_On_Non_Existing_Entity_Array_Input()
    {
        $data = ['id' => 123, 'name' => 'Foo'];
        CountryFactory::findOrMake($data)->persist();
        $this->assertSame(1, CountryFactory::find()->where($data)->count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testFindOrMake_On_Non_Existing_Entity_Entity_Input()
    {
        $data = CountryFactory::make(['id' => 123, 'name' => 'Foo'])->getEntity();
        CountryFactory::findOrMake($data)->persist();
        $this->assertSame(1, CountryFactory::find()->where($data->toArray())->count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testFindOrMake_On_Non_Existing_Entity_Factory_Input()
    {
        $data = ['id' => 123, 'name' => 'Foo'];
        $factory = CountryFactory::make($data);
        CountryFactory::findOrMake($factory)->persist();
        $this->assertSame(1, CountryFactory::find()->where($data)->count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testFindOrMake_On_Existing_Entity_Array_Input()
    {
        $country = CountryFactory::make()->persist();
        CountryFactory::findOrMake($country->toArray())->persist();
        $this->assertSame(1, CountryFactory::find()->where($country->toArray())->count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testFindOrMake_On_Existing_Entity_Entity_Input()
    {
        $country = CountryFactory::make()->persist();
        CountryFactory::findOrMake($country)->persist();
        $this->assertSame(1, CountryFactory::find()->where($country->toArray())->count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testFindOrMake_On_Existing_Entity_Factory_Input()
    {
        $data = CountryFactory::make()->persist()->toArray();
        $factory = CountryFactory::make($data);
        CountryFactory::findOrMake($factory)->persist();
        $this->assertSame(1, CountryFactory::find()->where($data)->count());
        $this->assertSame(1, CountryFactory::count());
    }

    public function testFindOrMake_On_Existing_Entity_With_Deep_Array_Data()
    {
        $this->expectException(FixtureFactoryException::class);
        $this->expectExceptionMessage(
            'Ensure that the data passed to findOrMake() is an array of string and integers'
        );
        $country = CountryFactory::make()->with('Cities')->persist();
        CountryFactory::findOrMake($country)->persist();
        $this->assertSame(0, CountryFactory::count());
    }

    public function testFindOrMake_On_Existing_Entity_Multiple_Input()
    {
        $name1 = 'Foo1';
        $name2 = 'Foo2';
        $name3 = 'Foo3';
        $name4 = 'Foo4';
        $data = [
            CountryFactory::make(['name' => $name1])->persist(),
            CountryFactory::make(['name' => $name2])->getEntity(),
            CountryFactory::make(['name' => $name3]),
            CountryFactory::make(['name' => $name4])->persist()->toArray(),
        ];
        CountryFactory::findOrMake($data)->persist();
        $this->assertSame(1, CountryFactory::find()->where(['name' => $name1])->count());
        $this->assertSame(1, CountryFactory::find()->where(['name' => $name2])->count());
        $this->assertSame(1, CountryFactory::find()->where(['name' => $name3])->count());
        $this->assertSame(1, CountryFactory::find()->where(['name' => $name4])->count());
    }

}
