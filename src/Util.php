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

namespace CakephpFixtureFactories;

use Cake\Core\Configure;
use Cake\Database\Driver\Postgres;
use Cake\Utility\Inflector;
use CakephpFixtureFactories\Factory\BaseFactory;

class Util
{
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
    public static function getFactoryNamespace($plugin = null): string
    {
        if (Configure::read('TestFixtureNamespace')) {
            return Configure::read('TestFixtureNamespace');
        } else {
            return (
                $plugin ?
                    $plugin :
                    Configure::read('App.namespace', 'App')
                ) . '\Test\Factory';
        }
    }

    /**
     * Return the class of the factory from a model name
     *
     * @param string $modelName Name of the model
     * @return string
     */
    public static function getFactoryClassFromModelName(string $modelName): string
    {
        $cast = explode('.', $modelName);
        $plugin = null;
        if (count($cast) === 2) {
            $plugin = $cast[0];
            $modelName = $cast[1];
        } else {
            $modelName = $cast[0];
        }

        return self::getFactoryNamespace($plugin) . '\\' . self::getFactoryNameFromModelName($modelName);
    }

    /**
     * @param \CakephpFixtureFactories\Factory\BaseFactory $factory Instance of factory
     * @return bool
     */
    public static function isRunningOnPostgresql(BaseFactory $factory): bool
    {
        return $factory->getRootTableRegistry()->getConnection()->config()['driver'] === Postgres::class;
    }
}
