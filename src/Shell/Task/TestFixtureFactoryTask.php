<?php


namespace TestFixtureFactories\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Plugin as CorePlugin;
use Cake\Filesystem\Folder;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class TestFixtureFactoryTask extends SimpleBakeTask
{
    /**
     * path to Factory directory
     *
     * @var string
     */
    public $pathFragment = 'tests/Factory/';
    /**
     * @var string path to the Table dir
     */
    public $pathToTableDir = 'src/Model/Table/';
    /**
     * @var Table
     */
    private $table;

    public function name()
    {
        return 'test_fixture_factory';
    }

    public function fileName($modelName)
    {
        return $this->getFactoryNameFromModelName($modelName) . '.php';
    }

    public function template()
    {
        return 'TestFixtureFactories.factory';
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
     * @return self
     */
    public function setTable(string $tableName)
    {
        if ($this->plugin) {
            $tableName = $this->plugin . ".$tableName";
        }
        $this->table = TableRegistry::getTableLocator()->get($tableName);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        if (isset($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . $this->pathFragment;
        } else {
            $path = ROOT . DS . $this->pathFragment;
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
            $path = $this->_pluginPath($this->plugin) . $this->pathToTableDir;
        } else {
            $path = ROOT . DS . $this->pathToTableDir;
        }

        return str_replace('/', DS, $path);
    }

    /**
     * List the tables
     * @return array
     */
    public function getTableList()
    {
        $dir = new Folder($this->getModelPath());
        $tables = $dir->find('.*Table.php', true);
        return array_map(function ($a) {
            return preg_replace('/Table.php$/', '', $a);
        }, $tables);
    }

    public function getFactoryNameFromModelName(string $name)
    {
        return Inflector::singularize(ucfirst($name)) . 'Factory';
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
     * @return null|bool
     */
    public function main($model = null)
    {
        if (isset($this->params['plugin'])) {
            $parts = explode('/', $this->params['plugin']);
            $this->plugin = implode('/', array_map([$this, '_camelize'], $parts));
            if (strpos($this->plugin, '\\')) {
                $this->abort('Invalid plugin namespace separator, please use / instead of \ for plugins.');

                return false;
            }
        }

        $model = $this->_getName($model);

        if ($this->param('all')) {
            return $this->bake('all');
        }

        if (empty($model)) {
            $this->out('Choose a table from the following, choose -a for all, or -h for help:');
            foreach ($this->getTableList() as $table) {
                $this->out('- ' . $table);
            }

            return true;
        }

        $this->bake($model);
    }

    /**
     * {@inheritDoc}
     */
    public function bake($modelName)
    {
        if ($modelName === 'all') {
            return $this->bakeAllModels();
        }

        $this->setTable($modelName);

        $this->handleFactoryWithSameName($modelName);

        $this->setViewVars($modelName);

        return parent::bake($modelName);
    }

    /**
     * This is overwritten because it is incompatible
     * with the way the present factory bakes
     * @return array|null
     */
    public function templateData()
    {}

    /**
     * Send view variables to the twig template
     * @param string $modelName
     */
    public function setViewVars(string $modelName)
    {
        $this->BakeTemplate->set([
            'rootTableRegistryName' => $modelName,
            'factoryEntity' => Inflector::singularize($modelName),
            'factory' => Inflector::singularize($modelName) . 'Factory',
            'namespace' => $this->getFactoryNamespace(),
        ]);

        if ($this->param('methods')) {
            $associations = $this->getAssociations();
            $this->BakeTemplate->set('toOne', $associations['toOne']);
            $this->BakeTemplate->set('toMany', $associations['toMany']);
        }
    }

    /**
     * Returns the one and many association for a given model
     * @param string $modelName
     * @return array
     */
    public function getAssociations()
    {
        $associations = [
            'toOne' => [],
            'toMany' => [],
        ];

        foreach($this->getTable()->associations() as $association) {
            $name = $association->getClassName() ?? $association->getName();
            switch($association->type()) {
                case 'oneToOne':
                case 'manyToOne':
                    $associations['toOne'][$association->getName()] = $this->getFactoryWithSpaceName($name);
                    break;
                case 'oneToMany':
                case 'manyToMany':
                    $associations['toMany'][$association->getName()] = $this->getFactoryWithSpaceName($name);
                    break;
            }
        }
        return $associations;
    }

    /**
     * Namespace where the factory belongs
     * @return string
     */
    public function getFactoryNamespace()
    {
        return (
            $this->plugin ?
            $this->plugin :
            Configure::read('App.namespace', 'App')
        ) . '\Test\Factory';
    }

    /**
     * @param string $modelName
     * @return string
     */
    public function getFactoryWithSpaceName(string $associationClass)
    {
        $cast = explode('.', $associationClass);
        if (count($cast) === 2) {
            $app =  $cast[0];
            $factory = $cast[1];
        } else {
            $app =  Configure::read('App.namespace', 'App');
            $factory = $cast[0];
        }
        return '\\' . $app . '\Test\Factory\\' . $this->getFactoryNameFromModelName($factory);
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
    public function getOptionParser()
    {
        $name = ($this->plugin ? $this->plugin . '.' : '') . $this->name;
        $parser = new ConsoleOptionParser($name);

        $bakeThemes = [];
        foreach (CorePlugin::loaded() as $plugin) {
            $path = CorePlugin::classPath($plugin);
            if (is_dir($path . 'Template' . DS . 'Bake')) {
                $bakeThemes[] = $plugin;
            }
        }

        $parser->setDescription(
            'Bake factory class.'
        )
            ->addArgument('factory', [
                'help' => 'Name of the factory to bake (singular, without the `Factory` suffix). ' .
                    'You can use Plugin.name to bake plugin factories.',
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
                'help' => 'Include methods based on the table relations',
            ]);

        return $parser;
    }
}
