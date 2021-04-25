<?php
declare(strict_types=1);

/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2020 Juan Pablo Ramirez and Nicolas Masson
 * @link          https://webrider.de/
 * @since         2.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakephpFixtureFactories\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;
use CakephpFixtureFactories\Error\FactoryNotFoundException;
use CakephpFixtureFactories\Error\FixtureFactoryException;
use CakephpFixtureFactories\Error\PersistenceException;
use CakephpFixtureFactories\Factory\BaseFactory;
use CakephpFixtureFactories\Factory\FactoryAwareTrait;
use CakephpTestSuiteLight\FixtureInjector;
use CakephpTestSuiteLight\FixtureManager;

class PersistCommand extends Command
{
    use FactoryAwareTrait;

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'fixture_factories_persist';
    }

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Helper to persist test fixtures on the command line')
            ->addArgument('factory', [
                'help' => 'The factory to persist',
                'required' => true,
            ])
            ->addOption('plugin', [
                'help' => 'Fetch the factory in a plugin',
                'short' => 'p',
            ])
            ->addOption('connection', [
                'help' => 'Persist into this connection',
                'short' => 'c',
                'default' => 'test',
            ])
            ->addOption('number', [
                'help' => 'Number of entities to persist',
                'short' => 'n',
                'default' => 1,
            ])
            ->addOption('dry-run', [
                'help' => 'Name of the phpunit config file (per default phpunit.xml.dist)',
                'short' => 'd',
            ])
        ;

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $factory = null;
        try {
            $factory = $this->parseFactory($args);
            $this->attachMethod($args, $factory);
        } catch (FactoryNotFoundException $e) {
            $io->error($e->getMessage());
            $this->abort();
        }

        $connection = $args->getOption('connection') ?? 'test';
        $this->aliasConnection($connection, $factory);
        $this->setTimes($args, $factory);
        $this->attachMethod($args, $factory);

        try {
            $factory->persist();
        } catch (PersistenceException $e) {
            $io->error($e->getMessage());
            $this->abort();
        }

        $factory = get_class($factory);
        $io->success("{$factory} persisted on '{$connection}' connection.");

        return self::CODE_SUCCESS;
    }

    /**
     * @param Arguments $args The command arguments
     * @return BaseFactory
     * @throws \CakephpFixtureFactories\Error\FactoryNotFoundException if the factory could not be found
     */
    public function parseFactory(Arguments $args): BaseFactory
    {
        $factoryString = $args->getArgument('factory');

        if (is_subclass_of($factoryString,BaseFactory::class)) {
            return $factoryString::make();
        }
        if ($plugin = $args->getOption('plugin')) {
            $factoryString = $plugin . '.' . $factoryString;
        }

        return $this->getFactory($factoryString);
    }

    public function setTimes(Arguments $args, BaseFactory $factory): BaseFactory
    {
        return $factory->setTimes($args->getOption('number') ?? 1);
    }

    /**
     * @param Arguments $args
     * @param BaseFactory $factory
     * @return BaseFactory
     * @throws FactoryNotFoundException if the method is not found in the factory
     */
    public function attachMethod(Arguments $args, BaseFactory $factory): BaseFactory
    {
        $method = $args->getOption('method');

        if ($method === null) {
            return $factory;
        }
        if (!method_exists($factory, $method)) {
            $className = get_class($factory);
            throw new FactoryNotFoundException("The method {$method} was not found in {$className}.");
        }

        return $factory->{$method}();
    }

    /**
     * Sets the connection passed in argument as the target connection,
     * overwriting the table's default connection.
     *
     * @param string $connection Connection name
     * @param BaseFactory $factory
     */
    public function aliasConnection(string $connection, BaseFactory $factory)
    {
        ConnectionManager::alias(
            $connection,
            $factory->getRootTableRegistry()->getConnection()->configName()
        );
    }
}
