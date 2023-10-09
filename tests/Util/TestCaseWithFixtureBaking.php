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

namespace CakephpFixtureFactories\Test\Util;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Command\BakeFixtureFactoryCommand;

class TestCaseWithFixtureBaking extends TestCase
{
    /**
     * ConsoleIo mock
     *
     * @var ConsoleIo
     */
    public $io;

    /**
     * Test subject
     *
     * @var BakeFixtureFactoryCommand
     */
    public $FactoryCommand;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->dropTestFactories();
        $this->io = new ConsoleIo();
        $this->io->level(ConsoleIo::QUIET);
        $this->FactoryCommand = new BakeFixtureFactoryCommand();
    }

    private function dropTestFactories()
    {
        $factoryFolder = TESTS . 'Factory';
        array_map('unlink', glob("$factoryFolder/*.*"));
        $pluginFactoryFolder = Configure::read('App.paths.plugins')[0] . 'TestPlugin' . DS . 'tests' . DS . 'Factory';
        array_map('unlink', glob("$pluginFactoryFolder/*.*"));
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->FactoryCommand);

        parent::tearDown();
    }

    /**
     * In order to have phpstan analyse properly our code,
     * we bake again all the fixtures. This way, the baked classes get
     * analysed as well
     */
    public static function tearDownAfterClass(): void
    {
        /** @psalm-suppress InternalMethod */
        $test = new self('SomeTest');
        $test->setUp();
        $test->bake([], ['methods' => true, 'all' => true]);
        $test->bake([], ['plugin' => 'TestPlugin', 'all' => true, 'methods' => true,]);
    }

    protected function bake(array $args = [], array $options = [], array $argNames = ['model'])
    {
        $options['force'] = $options['force'] ?? true;
        $options['quiet'] = $options['quiet'] ?? true;
        $options['connection'] = $options['connection'] ?? 'default';
        $args = new Arguments($args, $options, $argNames);
        $this->assertEquals(0, $this->FactoryCommand->execute($args, $this->io));
    }
}
