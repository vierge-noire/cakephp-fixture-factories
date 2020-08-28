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
use CakephpFixtureFactories\TestSuite\FixtureInjector;
use CakephpFixtureFactories\TestSuite\FixtureManager;

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
            ->addArgument('plugin', [
                'help' => 'Set configs in a plugin',
                'short' => 'p',
            ])
            ->addArgument('file', [
                'help' => 'Name of your phpunit config file (per default phpunit.xml.dist)',
                'short' => 'f',
            ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $filePath = $this->getPhpunitFilePath($args, $io);
        $this->replaceListenersInPhpunitXmlFile($filePath, $io);
        $io->success("The listener was successfully replaced in $filePath.");
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
            $io->abort("The phpunit config file $path could not be found.");
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
            $string = file_get_contents($filePath);
        } catch (\Exception $exception) {
            $io->abort($exception->getMessage());
        }

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
