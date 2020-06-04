<?php
declare(strict_types=1);

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
