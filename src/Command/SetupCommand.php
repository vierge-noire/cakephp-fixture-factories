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
namespace CakephpFixtureFactories\Command;

use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpTestSuiteLight\FixtureInjector;
use CakephpTestSuiteLight\FixtureManager;

class SetupCommand extends Command
{
    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'fixture_factories_setup';
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Helper to setup your phpunit xml file')
            ->addOption('plugin', [
                'help' => 'Set configs in a plugin',
                'short' => 'p',
            ])
            ->addOption('file', [
                'help' => 'Name of the phpunit config file (per default phpunit.xml.dist)',
                'short' => 'f',
            ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $filePath = $this->getPhpunitFilePath($args, $io);
        $this->replaceListenersInPhpunitXmlFile($filePath, $io);
        $io->success("The listener was successfully replaced in $filePath.");
        return 0;
    }

    public function getPhpunitFilePath(Arguments $args, ConsoleIo $io): string
    {
        $fileName = $args->getOption('file') ?? 'phpunit.xml.dist';

        if ($plugin = $args->getOption('plugin')) {
            $path = ROOT . DS . 'plugins' . DS . $plugin . DS . $fileName;
        } else {
            $path = ROOT . DS . $fileName;
        }

        if (!file_exists($path)) {
            $io->error("The phpunit config file $path could not be found.");
            return '';
        } else {
            return $path;
        }
    }

    /**
     * Replaces the listeners and injectors in $filePath
     * @param string $filePath
     * @param ConsoleIo $io
     */
    public function replaceListenersInPhpunitXmlFile(string $filePath, ConsoleIo $io)
    {
        try {
            $this->replaceListenerInString(
                $filePath,
                file_get_contents($filePath)
            );
        } catch (\Exception $exception) {
            throw new FixtureFactoryException("$filePath could not be found.");
        }
    }

    protected function replaceListenerInString(string $filePath, string $string)
    {
        $this->existsInString(\Cake\TestSuite\Fixture\FixtureInjector::class, $string, $filePath);
        $this->existsInString(\Cake\TestSuite\Fixture\FixtureManager::class, $string, $filePath);

        $string = str_replace(\Cake\TestSuite\Fixture\FixtureInjector::class, FixtureInjector::class, $string);
        $string = str_replace(\Cake\TestSuite\Fixture\FixtureManager::class, FixtureManager::class, $string);

        file_put_contents($filePath, $string);
    }

    /**
     * Ensure that a string is well found in a file
     * @param string $search
     * @param string $subject
     * @param string $filePath
     */
    protected function existsInString(string $search, string $subject, string $filePath)
    {
        if (strpos($subject, $search) === false) {
            throw new FixtureFactoryException("$search could not be found in $filePath.");
        }
    }

    /**
     * Replace the listeners using the native XML DOM tool
     * The disadvantage is that this changes the indentation
     * and empty lines
     * @param string $filePath
     * @param string $string
     * @deprecated
     */
    protected function replaceListenerWithDOMDocument(string $filePath, string $string)
    {
        $dom = new \SimpleXMLElement($string);
        $dom->listeners = '';
        $listener = $dom->listeners->addChild('listener');
        $listener->addAttribute('class', FixtureInjector::class);
        $arguments = $listener->addChild('arguments');
        $arguments->addChild('object')->addAttribute('class', FixtureManager::class);

        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($dom->asXML());
        $doc->save($filePath);
    }
}
