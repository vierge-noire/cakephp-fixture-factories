<?php
namespace TestFixtureFactories\TestSuite;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\MissingDatasourceConfigException;
use Cake\Filesystem\File;

/**
 * FixtureManager shell command.
 */
class FixtureManagerShell extends Shell
{
    public function init($runWithMigrations = false)
    {
        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            // Drop the tables of a test connection
            if (strpos($connectionName, 'test') === 0) {
                $this->dropTables($connectionName);
            } else {
                continue;
            }
        }

        if ($runWithMigrations == false || $runWithMigrations == 0) {
            foreach ($connections as $connectionName) {

                $masterDB = ($connectionName === 'test') ? 'default' : str_replace('test_', '', $connectionName);

                try {
                    // Dump the schema form the exiting DBs
                    $this->dumpDBSchema(
                        ConnectionManager::get($masterDB)->config()['database'],
                        ConnectionManager::get($connectionName)->config()['database']
                    );
                } catch (MissingDatasourceConfigException $e) {
                    $this->out($e->getMessage());
                }
            }
        } else {
             $this->dispatchShell('migrations migrate -c test --no-lock');
             $this->dispatchShell('migrations migrate -c test -p ' . Configure::read('site_plugin') . ' --no-lock');
        }

        $this->dispatchShell('cache clear _cake_model_');
        $this->dispatchShell('schema_cache clear');
    }

    public function dropTables($connectionName)
    {
        $connection = ConnectionManager::get($connectionName);

        $tables = $connection->getSchemaCollection()->listTables();

        if (!empty($tables)) {
            $connection->execute("DROP TABLE `" . implode('`, `', $tables) . "`;");
        }
        $this->out("Tables of connection '$connectionName' dropped");
    }

    /**
     * Dump the schema from a DB into another DB
     * @param $fromDB
     * @param $toDB
     */
    public function dumpDBSchema($fromDB, $toDB)
    {
        $fileName = TMP . 'test_schema.sql';

        $this->out("Dumping schema from $fromDB");
        exec("mysqldump -u root -pvagrant --no-data --databases $fromDB > $fileName");
        exec("sed -i 's/`$fromDB`/`$toDB`/g' $fileName");

        $this->out("Copying schema to $toDB");
        exec("mysql -u root -pvagrant $toDB < $fileName");

        $file = new File($fileName);
        $file->delete();
    }
}
