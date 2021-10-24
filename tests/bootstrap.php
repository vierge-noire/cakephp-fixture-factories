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

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;
use CakephpTestMigrator\Migrator;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
define('ROOT', dirname(__DIR__));
define('APP_DIR', 'src');
define('APP_PATH', ROOT . DS . 'TestApp' . DS);
define('VENDOR_PATH', ROOT . DS . 'vendor' . DS);
define('TEMPLATE_PATH_CAKE_4', ROOT . DS . 'templates' . DS);
define('TEMPLATE_PATH_CAKE_3', ROOT . DS . APP_DIR . DS . 'Template' . DS);

define('TMP', ROOT . DS . 'tmp' . DS);
define('LOGS', TMP . 'logs' . DS);
define('CACHE', TMP . 'cache' . DS);
define('SESSIONS', TMP . 'sessions' . DS);

define('CAKE_CORE_INCLUDE_PATH', VENDOR_PATH . 'cakephp' . DS . 'cakephp');
define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('CORE_TESTS', ROOT . DS . 'tests' . DS);
define('CORE_TEST_CASES', CORE_TESTS . 'TestCase');
define('TEST_APP', CORE_TESTS . 'TestApp' . DS);

// Point app constants to the test app.
define('APP', TEST_APP . 'src' . DS);
define('TESTS', TEST_APP . 'tests' . DS);
define('WWW_ROOT', TEST_APP . 'webroot' . DS);
define('CONFIG', TEST_APP . 'config' . DS);

// phpcs:disable
@mkdir(LOGS);
@mkdir(SESSIONS);
@mkdir(CACHE);
@mkdir(CACHE . 'views');
@mkdir(CACHE . 'models');
// phpcs:enable

require_once CORE_PATH . 'config/bootstrap.php';

Configure::write('debug', true);
Configure::write('App', [
    'namespace' => 'TestApp',
    'paths' => [
        'plugins' => [TEST_APP . 'plugins' . DS],
        'templates' => [
            TEST_APP . 'templates' . DS,
            TEMPLATE_PATH_CAKE_4,
            TEMPLATE_PATH_CAKE_3,
        ],
    ],
]);

Cache::setConfig([
    '_cake_core_' => [
        'engine' => 'File',
        'prefix' => 'cake_core_',
        'serialize' => true,
    ],
    '_cake_model_' => [
        'engine' => 'File',
        'prefix' => 'cake_model_',
        'serialize' => true,
    ],
]);

$loadEnv = function(string $fileName) {
    if (file_exists($fileName)) {
        $dotenv = new \josegonzalez\Dotenv\Loader($fileName);
        $dotenv->parse()
            ->putenv(true)
            ->toEnv(true)
            ->toServer(true);
    }
};

if (!getenv('DB_DRIVER')) {
    putenv('DB_DRIVER=Sqlite');
}
$driver =  getenv('DB_DRIVER');
$testDir = ROOT . DS . 'tests' . DS;

if (!file_exists("$testDir.env")) {
    @copy("$testDir.env.$driver", "$testDir.env");
}

/**
 * Read .env file(s).
 */
$loadEnv("$testDir.env");

// Re-read the driver
$driver =  getenv('DB_DRIVER');
echo "Using driver $driver \n";

$dbConnection = [
    'className' => 'Cake\Database\Connection',
    'driver' => 'Cake\Database\Driver\\' . $driver,
    'persistent' => false,
    'host' => getenv('DB_HOST'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PWD'),
    'database' => getenv('DB_DATABASE'),
    'encoding' => 'utf8',
    'timezone' => 'UTC',
    'cacheMetadata' => true,
    'quoteIdentifiers' => true,
    'log' => false,
    //'init' => ['SET GLOBAL innodb_stats_on_metadata = 0'],
    'url' => env('DATABASE_TEST_URL', null),
    'migrations' => [
        ['connection' => 'test'],
        ['plugin' => 'TestPlugin'],
    ]
];

ConnectionManager::setConfig('test', $dbConnection);
$dbConnection['dummy_key'] = 'DummyKeyValue';
ConnectionManager::setConfig('dummy', $dbConnection);

Inflector::rules('singular', ['/(ss)$/i' => '\1']);

Plugin::load('TestPlugin');
Plugin::load('CakephpFixtureFactories');
Migrator::migrate();
