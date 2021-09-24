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
namespace CakephpFixtureFactories\Test\TestCase\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\Exception\StopException;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Command\SetupCommand;

/**
 * App\Shell\Task\FactoryTask Test Case
 * @deprecated The SetupCommand is not required anymore
 */
class SetupCommandTest extends TestCase
{
    /**
     * @var string
     */
    public $testPluginName = 'TestPlugin';

    /**
     * @var string
     */
    public $testFixturePath = ROOT . DS . 'tests' . DS . 'Fixture' . DS;

    /**
     * @var SetupCommand
     */
    public $setupCommand;

    /**
     * @var ConsoleIo
     */
    public $io;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupCommand = new SetupCommand();
        $this->io  = new ConsoleIo();
    }

    public function tearDown(): void
    {
        unset($this->setupCommand);
        unset($this->io);
        parent::tearDown();
    }

    private function createTmpPhpunitFile(string $source, string $target)
    {
        copy(
            $this->testFixturePath . $source,
            $this->testFixturePath . $target
        );
    }

    private function createPhpunitFile(string $source, string $target)
    {
        copy(
            $this->testFixturePath . $source,
            ROOT . DS . $target
        );
    }

    private function removeTmpPhpunitFile(string $tmpFileName)
    {
        unlink($this->testFixturePath . $tmpFileName);
    }

    private function removePhpunitFile(string $tmpFileName)
    {
        unlink(ROOT . DS . $tmpFileName);
    }

    protected function exec(array $args = [], array $options = [], array $argNames = [])
    {
        $args = new Arguments($args, $options, $argNames);
        $this->assertEquals(0, $this->setupCommand->execute($args, $this->io));
    }

    public function dataProviderReplaceListenersInPhpunitXmlFile()
    {
        return [
            ['phpunit_default.xml.dist'],
            ['phpunit_minimalist.xml.dist'],
        ];
    }

    /**
     * @dataProvider dataProviderReplaceListenersInPhpunitXmlFile
     * @param string $phpunitFile
     */
    public function testReplaceListenersInPhpunitXmlFile(string $phpunitFile)
    {
        $cmd = new SetupCommand();
        $tmpFileName = 'foo';
        $this->createTmpPhpunitFile($phpunitFile, $tmpFileName);
        $filePath = $this->testFixturePath . $tmpFileName;
        $cmd->replaceListenersInPhpunitXmlFile($filePath, $this->io);

        $content = file_get_contents($filePath);

        $expected = [
            '<listeners>',
            '<listener class="CakephpTestSuiteLight\FixtureInjector">',
            '<arguments>',
            '<object class="CakephpTestSuiteLight\FixtureManager"/>',
            '</arguments>',
            '</listener>',
            '</listeners>',
        ];
        foreach ($expected as $ex) {
            $this->assertStringContainsString($ex, $content);
        }

        $this->removeTmpPhpunitFile($tmpFileName);
    }

    public function testReplaceListenersInPhpunitXmlFileWrongFile()
    {
        $this->expectException(StopException::class);
        $cmd = new SetupCommand();
        $cmd->replaceListenersInPhpunitXmlFile('abc', $this->io);
    }

    /**
     * @dataProvider dataProviderReplaceListenersInPhpunitXmlFile
     * @param string $phpunitFile
     */
    public function testExecute(string $phpunitFile)
    {
        $tmpFileName = 'foo';
        $this->createPhpunitFile($phpunitFile, $tmpFileName);
        $this->exec([], ['file' => $tmpFileName]);
        $this->removePhpunitFile($tmpFileName);
    }

    public function testExecuteWithWrongFile()
    {
        $this->expectException(StopException::class);
        $this->exec([], ['file' => 'foo']);
    }

    public function testExecuteWithPlugin()
    {
        $pluginName = 'Foo';
        $this->expectException(StopException::class);
        $this->expectExceptionMessage("plugins/$pluginName/phpunit.xml.dist could not be found.");
        $this->exec([], ['plugin' => $pluginName]);
    }

    public function testExecuteWithFile()
    {
        $fileName = 'Foo';
        $fullPath = ROOT . DS . $fileName;
        $this->expectException(StopException::class);
        $this->expectExceptionMessage("$fullPath could not be found.");
        $this->exec([], ['file' => $fileName]);
    }

    public function testExecuteWithFileAndPlugin()
    {
        $fileName = 'Foo';
        $pluginName = 'Bar';
        $fullPath = ROOT . DS . 'plugins' . DS . $pluginName . DS . $fileName;
        $this->expectException(StopException::class);
        $this->expectExceptionMessage("$fullPath could not be found.");
        $this->exec([], ['file' => $fileName, 'plugin' => $pluginName]);
    }
}
