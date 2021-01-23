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
    static public function getFactoryNameFromModelName(string $name): string
    {
        return Inflector::singularize(ucfirst($name)) . 'Factory';
    }

    /**
     * Namespace where the factory belongs
     * @param string|null $plugin
     * @return string
     */
    static public function getFactoryNamespace($plugin = null): string
    {
        if (Configure::read('TestFixtureNamespace')) {
            return Configure::read('TestFixtureNamespace');
        } else {
            return (
                $plugin ?
                    str_replace('/', '\\', $plugin) :
                    Configure::read('App.namespace', 'App')
                ) . '\Test\Factory';
        }
    }

    static public function getFactoryClassFromModelName(string $modelName): string
    {
        $cast = explode('.', $modelName);
        $plugin = null;
        if (count($cast) === 2) {
            $plugin =  $cast[0];
            $modelName = $cast[1];
        } else {
            $modelName = $cast[0];
        }
        return self::getFactoryNamespace($plugin) . '\\' . self::getFactoryNameFromModelName($modelName);
    }

    static public function isRunningOnPostgresql(BaseFactory $factory): bool
    {
        return $factory->getRootTableRegistry()->getConnection()->config()['driver'] === Postgres::class;
    }
}