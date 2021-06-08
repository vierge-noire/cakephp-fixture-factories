<?php
declare(strict_types=1);

namespace CakephpFixtureFactories\Factory;

use Cake\Core\Configure;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Error\FactoryNotFoundException;

trait FactoryAwareTrait
{
    /**
     * Returns a factory instance from factory or model name
     *
     * Additionnal arguments are passed *as is* to `BaseFactory::make`
     *
     * @param  string           $name          Factory or model name
     * @param  string|array[]   ...$arguments  Additionnal arguments for `BaseFactory::make`
     * @return \CakephpFixtureFactories\Factory\BaseFactory
     * @throws \CakephpFixtureFactories\Error\FactoryNotFoundException if the factory could not be found
     * @see \CakephpFixtureFactories\Factory\BaseFactory::make
     */
    public function getFactory(string $name, ...$arguments): BaseFactory
    {
        $factoryClassName = $this->getFactoryClassName($name);

        if (class_exists($factoryClassName)) {
            return $factoryClassName::make(...$arguments);
        }

        throw new FactoryNotFoundException("Unable to locate factory class $factoryClassName");
    }

    /**
     * Converts factory or model name to a fully qualified factory class name
     *
     * @param  string $name Factory or model name
     * @return string       Fully qualified class name
     */
    public function getFactoryClassName(string $name): string
    {
        // phpcs:disable
        @[$modelName, $plugin] = array_reverse(explode('.', $name));
        // phpcs:enable

        return $this->getFactoryNamespace($plugin) . '\\' . $this->getFactoryNameFromModelName($modelName);
    }

    /**
     * Returns the factory file name
     *
     * @param  string $name [description]
     * @return string       [description]
     */
    public function getFactoryFileName(string $name): string
    {
        return $this->getFactoryNameFromModelName($name) . '.php';
    }

    /**
     * Return the name of the factory from a model name
     *
     * @param string $modelName Name of the model
     * @return string
     */
    public static function getFactoryNameFromModelName(string $modelName): string
    {
        return Inflector::singularize(ucfirst($modelName)) . 'Factory';
    }

    /**
     * Namespace where the factory belongs
     *
     * @param string|null $plugin name of the plugin, or null if no plugin
     * @return string
     */
    public function getFactoryNamespace(?string $plugin = null): string
    {
        if (Configure::check('TestFixtureNamespace')) {
            return Configure::read('TestFixtureNamespace');
        } else {
            return (
                $plugin ?
                    str_replace('/', '\\', $plugin) :
                    Configure::read('App.namespace', 'App')
                ) . '\Test\Factory';
        }
    }
}
