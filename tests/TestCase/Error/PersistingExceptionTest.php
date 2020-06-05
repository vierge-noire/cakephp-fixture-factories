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
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\Test\Factory\AuthorFactory;


class PersistingExceptionTest extends TestCase
{
    public function testSaveWronglyBuiltEntity()
    {
        $this->expectException(PersistenceException::class);
        $factory = AuthorFactory::class;
        $this->expectExceptionMessage("Error in Factory $factory.");
        AuthorFactory::make(['id' => 1])->persist();
        AuthorFactory::make(['id' => 1])->persist();
    }

    public function testSaveWronglyBuiltEntities()
    {
        $this->expectException(PersistenceException::class);
        $factory = AuthorFactory::class;
        $this->expectExceptionMessage("Error in Factory $factory.");
        AuthorFactory::make(['id' => 1], 2)->persist();
    }
}
