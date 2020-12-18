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
namespace CakephpFixtureFactories\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Filesystem\Folder;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Util;
use ReflectionClass;

/**
 * FixtureFactory code generator.
 */
class FixtureFactoryTask extends SimpleBakeTask
{
    /**
     * path to Factory directory
     *
     * @var string
     */
    public $pathFragment = 'tests' . DS . 'Factory' . DS;
    /**
     * @var string path to the Table dir
     */
    public $pathToTableDir = 'Model' . DS . 'Table' . DS;
    /**
     * @var string
     */
    private $modelName;
    /**
     * @var Table
     */
    private $table;

    public function name(): string
    {
        return 'fixture_factory';
    }

    public function fileName($modelName): string
    {
        return Util::getFactoryNameFromModelName($modelName) . '.php';
    }

    public function template(): string
    {
        return 'CakephpFixtureFactories.fixture_factory';
    }

    /**
     * @return Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @param string $tableName
     * @return $this|bool
     */
    public function setTable(string $tableName)
    {
        if ($this->plugin) {
            $tableName = $this->plugin . ".$tableName";
        }
        $this->table = TableRegistry::getTableLocator()->get($tableName);
        try {
            $this->table->getSchema();
        } catch (\Exception $e) {
            $this->err($e->getMessage());
            return false;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        if (isset($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . $this->pathFragment;
        } else {
            $path = TESTS . 'Factory' . DS;
        }

        return str_replace('/', DS, $path);
    }

    /**
     * Locate tables
     * @return string|string[]
     */
    public function getModelPath()
    {

        if (isset($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . APP_DIR . DS . $this->pathToTableDir;
        } else {
            $path = APP . $this->pathToTableDir;
        }

        return str_replace('/', DS, $path);
    }

    /**
     * List the tables
     * @return array
     */
    public function getTableList(): array
    {
        $dir = new Folder($this->getModelPath());
        $tables = $dir->find('.*Table.php', true);

        $tables = array_map(function ($a) {
            return preg_replace('/Table.php$/', '', $a);
        }, $tables);

        foreach ($tables as $i => $table) {
            if (!$this->thisTableShouldBeBaked($table)) {
                unset($tables[$i]);
                echo "{$table} ignored";
            }
        }

        return $tables;
    }

    /**
     * Return false if the table is not found or is abstract, interface or trait
     * @param string $table
     * @return bool
     */
    public function thisTableShouldBeBaked(string $table): bool
    {

        $tableClassName = $this->plugin ? $this->plugin : Configure::read('App.namespace');
        $tableClassName .= "\Model\Table\\{$table}Table";

        try {
            $class = new ReflectionClass($tableClassName);
        } catch (\ReflectionException $e) {
            echo $e->getMessage();
            return false;
        }

        if ($class->isAbstract() || $class->isInterface() || $class->isTrait()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    private function bakeAllModels()
    {
        $tables = $this->getTableList();
        if (empty($tables)) {
            $this->err(sprintf('No tables were found at `%s`', $this->getModelPath()));
        } else {
            foreach ($tables as $table) {
                $this->bake($table);
            }
        }
        return '';
    }

    /**
     * Execution method always used for tasks
     * Handles dispatching to interactive, named, or all processes.
     *
     * @param string|null $model The name of the model to bake.
     * @return int
     */
    public function main($model = null): int
    {
        if ($this->param('plugin')) {
            $parts = explode('/', $this->param('plugin'));
            $this->plugin = implode('/', array_map([$this, '_camelize'], $parts));
            if (strpos($this->plugin, '\\')) {
                $this->abort('Invalid plugin namespace separator, please use / instead of \ for plugins.');

                return -1;
            }
        }

        if ($model) {
            $this->_getName($model);
        }

        if ($this->param('all')) {
            $this->bake('all');
            return 2;
        }

        if (empty($model)) {
            $this->out('Choose a table from the following, choose -a for all, or -h for help:');
            foreach ($this->getTableList() as $table) {
                $this->out('- ' . $table);
            }

            return 0;
        }

        $this->bake($model);
        return 1;
    }

    /**
     * {@inheritDoc}
     */
    public function bake($modelName): string
    {
        if ($modelName === 'all') {
            return $this->bakeAllModels();
        }

        $this->modelName = $modelName;

        $this->params['no-test'] = true;

        if ($this->setTable($modelName)) {
            $this->handleFactoryWithSameName($modelName);
             return parent::bake($modelName);
        } else {
            return "$modelName not found...";
        }
    }

    /**
     * Send variables to the view
     * @return array
     */
    public function templateData(): array
    {
        $data = [
            'rootTableRegistryName' => $this->plugin ? $this->plugin . '.' . $this->modelName : $this->modelName,
            'modelNameSingular' => Inflector::singularize($this->modelName),
            'modelName' => $this->modelName,
            'factory' => Inflector::singularize($this->modelName) . 'Factory',
            'namespace' => Util::getFactoryNamespace($this->plugin),
        ];
        $methods = [];
        if ($this->param('methods')) {
            $associations = $this->getAssociations();

            $data['toOne'] = $associations['toOne'];
            $methods = array_keys($associations['toOne']);

            $data['oneToMany'] = $associations['oneToMany'];
            $methods = array_merge(array_keys($associations['oneToMany']), $methods);

            $data['manyToMany'] = $associations['manyToMany'];
            $methods = array_merge(array_keys($associations['manyToMany']), $methods);

            array_walk($methods, function(&$value) { $value = "with$value"; } );
            $data['methods'] = $methods;
        }

        return $data;
    }

    /**
     * Returns the one and many association for a given model
     * @return array
     */
    public function getAssociations(): array
    {
        $associations = [
            'toOne' => [],
            'oneToMany' => [],
            'manyToMany' => [],
        ];

        foreach($this->getTable()->associations() as $association) {
            $modelName = $association->getClassName() ?? $association->getName();
            $factory = Util::getFactoryClassFromModelName($modelName);
            switch($association->type()) {
                case 'oneToOne':
                case 'manyToOne':
                    $associations['toOne'][$association->getName()] = $factory;
                    break;
                case 'oneToMany':
                    $associations['oneToMany'][$association->getName()] = $factory;
                    break;
                case 'manyToMany':
                    $associations['manyToMany'][$association->getName()] = $factory;
                    break;
            }
        }
        return $associations;
    }

    /**
     * @param string $name
     */
    public function handleFactoryWithSameName(string $name)
    {
        $factoryWithSameName = glob($this->getPath() . $name . '.php');
        if (!empty($factoryWithSameName)) {
            $force = $this->param('force');
            if (!$force) {
                $this->abort(
                    sprintf(
                        'A factory with the name `%s` already exists.',
                        $name
                    )
                );
            }

            $this->info(sprintf('A factory with the name `%s` already exists, it will be deleted.', $name));
            foreach ($factoryWithSameName as $factory) {
                $this->info(sprintf('Deleting factory file `%s`...', $factory));
                if (unlink($factory)) {
                    $this->success(sprintf('Deleted `%s`', $factory));
                } else {
                    $this->err(sprintf('An error occurred while deleting `%s`', $factory));
                }
            }
        }
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $name = ($this->plugin ? $this->plugin . '.' : '') . $this->name;
        $parser = new ConsoleOptionParser($name);

        $parser->setDescription(
            'Fixture factory generator.'
        )
            ->addArgument('model', [
                'help' => 'Name of the model the factory will create entities from (plural, without the `Table` suffix). '.
                    'You can use the Foo.Bars notation to bake a factory for the model Bars located in the plugin Foo. \n
                    Factories are located in the folder test\Factory of your app, resp. plugin.',
            ])
            ->addOption('plugin', [
                'short' => 'p',
                'help' => 'Plugin to bake into.',
            ])
            ->addOption('all', [
                'short' => 'a',
                'boolean' => true,
                'help' => 'Bake factories for all models.',
            ])
            ->addOption('force', [
                'short' => 'f',
                'boolean' => true,
                'help' => 'Force overwriting existing file if a factory already exists with the same name.',
            ])
            ->addOption('methods', [
                'short' => 'm',
                'boolean' => true,
                'help' => 'Include methods based on the table relations.',
            ]);

        return $parser;
    }
}
