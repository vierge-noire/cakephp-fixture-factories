<?php
namespace TestFixtureFactories\TestSuite;

use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\Exception\MissingDatasourceConfigException;
use Cake\Filesystem\File;

/**
 * FixtureManager shell command.
 */
class FixtureManagerShell extends Shell
{
    public function init()
    {
        $connections = ConnectionManager::configured();

        foreach ($connections as $connectionName) {
            if (strpos('test', $connectionName) === 0) {
                $this->dropTables($connectionName);
            }
        }

        // Activate this line when we have migrations ready to be imported
        // $this->dispatchShell('migrations migrate -c test --no-lock');

        // And deactivate these dumps
        $this->dumpDBSchema(
            ConnectionManager::get('default')->config()['database'],
            ConnectionManager::get('test')->config()['database']
        );

        try {
            $this->dumpDBSchema(
                ConnectionManager::get('newton_util')->config()['database'],
                ConnectionManager::get('test_newton_util')->config()['database']
            );
        } catch (MissingDatasourceConfigException $e) {
            $this->out($e->getMessage());
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
