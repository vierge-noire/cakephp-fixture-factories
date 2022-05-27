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

namespace CakephpFixtureFactories\Test\TestCase\ORM;

use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Factory\DataCompiler;
use CakephpFixtureFactories\ORM\FactoryTableBeforeSave;
use CakephpFixtureFactories\Test\Factory\CountryFactory;

class FactoryTableBeforeSaveTest extends TestCase
{
    public function testFindDuplicate()
    {
        $persistedCountry = CountryFactory::make()->persist();

        $unique_stamp = $persistedCountry->unique_stamp;
        $name = $persistedCountry->name;
        $id = $persistedCountry->id;

        // Has modified unique_stamp
        $duplicateCountry = CountryFactory::make(compact('id', 'unique_stamp', 'name'))->getEntity();
        $duplicateCountry->set(DataCompiler::MODIFIED_UNIQUE_PROPERTIES, ['unique_stamp']);
        $beforeSaver = new FactoryTableBeforeSave(CountryFactory::make()->getTable(), $duplicateCountry);
        $res = $beforeSaver->findDuplicate(compact('id'));
        $expect = compact('id', 'unique_stamp');
        $this->assertSame($expect, $res);

        // Has modified id
        $duplicateCountry = CountryFactory::make(compact('id', 'unique_stamp', 'name'))->getEntity();
        $duplicateCountry->set(DataCompiler::MODIFIED_UNIQUE_PROPERTIES, ['id']);
        $beforeSaver = new FactoryTableBeforeSave(CountryFactory::make()->getTable(), $duplicateCountry);
        $res = $beforeSaver->findDuplicate(compact('id'));
        $expect = compact('id');
        $this->assertSame($expect, $res);

        // Has modified id and unique_stamp
        $duplicateCountry = CountryFactory::make(compact('id', 'unique_stamp', 'name'))->getEntity();
        $duplicateCountry->set(DataCompiler::MODIFIED_UNIQUE_PROPERTIES, ['id', 'unique_stamp']);
        $beforeSaver = new FactoryTableBeforeSave(CountryFactory::make()->getTable(), $duplicateCountry);
        $res = $beforeSaver->findDuplicate(compact('id'));
        $expect = compact('id', 'unique_stamp');
        $this->assertSame($expect, $res);
    }

    public function testHandleUniqueFields()
    {
        $persistedCountry = CountryFactory::make()->persist();

        $unique_stamp = $persistedCountry->unique_stamp;
        $id = $persistedCountry->id;

        // Has modified id and unique_stamp
        $duplicateCountry = CountryFactory::make(compact('id', 'unique_stamp'))->getEntity();
        $duplicateCountry->set(DataCompiler::MODIFIED_UNIQUE_PROPERTIES, ['id', 'unique_stamp']);
        $duplicateCountry->set(DataCompiler::IS_ASSOCIATED, true);

        $beforeSaver = new FactoryTableBeforeSave(CountryFactory::make()->getTable(), $duplicateCountry);
        $beforeSaver->handleUniqueFields();

        $this->assertSame(null, $duplicateCountry->get(DataCompiler::IS_ASSOCIATED));
        $this->assertSame(null, $duplicateCountry->get(DataCompiler::MODIFIED_UNIQUE_PROPERTIES));
        $this->assertSame(false, $duplicateCountry->get('name') == $persistedCountry->get('name'));
        $this->assertSame($duplicateCountry->get('id'), $persistedCountry->get('id'));
        $this->assertSame($duplicateCountry->get('unique_stamp'), $persistedCountry->get('unique_stamp'));
    }
}
