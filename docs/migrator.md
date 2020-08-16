## The Migrator

The traditional CakePHP fixtures both create the schema of your test DBs and populate them with fixtures. The present package only cares about populating the test DBs on the fly.

### Setting 

The package proposes a tool to run your [migrations](https://book.cakephp.org/migrations/3/en/index.html) once prior to the tests. In order to do so,
you may place the following in your `tests/bootstrap.php`:
```$xslt
\CakephpFixtureFactories\TestSuite\Migrator::migrate();
```
This command will ensure that your migrations are well run and keeps the test DB(s) up to date. Since tables are truncated but never dropped by the present package's fixture manager, migrations will be run strictly when needed, namely only after a new migration was created by the developer.

The `Migrator`approach presents the following advantages:
* it improves the speed of the test suites by avoiding the creation and dropping of tables between each test case classes,
* it eases the maintenance of your tests, since regular and test DBs are managed the same way,
* it indirectly tests your migrations.

### Multiple migrations settings

Should you have migrations at different places, or with connections other than the default ones, you can configure these in a `config/fixture_factories.php` file similar to the following:
```$xslt
<?php

return [   
    'TestFixtureMigrations' => [
        ['connection' => 'test'],       // this is the default migration configuration that you now have to include, if needed
        ['plugin' => 'FooPlugin'],      // these are the migrations of the FooPlugin
        ['source' => 'BarFolder']       // these are the migrations located in a BarFolder
        ...
    ],
];
```

Alternatively, you can also pass the various migrations directly in the `Migrator` instanciation in your `tests/bootstrap.php`:
```$xslt
\CakephpFixtureFactories\TestSuite\Migrator::migrate([
    ['connection' => 'test'],       
    ['plugin' => 'FooPlugin'],      
    ['source' => 'BarFolder'],
    ...
 ]);
```

If you ever switched to a branch with different migrations, the `Migrator` will automatically drop the tables where needed, and re-run the migrations. Switching branches therefore
does not require any intervention on your side.

### Running migrations before each tests

The tables of the database get emptied prior to each tests. If you use migrations to seed data in your data base, you may want to run these migrations before each tests.

In order to do that, in the previously mentioned `config/fixture_factories.php` file, enter:

```
'TestFixtureMarkedNonMigrated' => [
    ['source' => 'FolderWithMySeeds'],
    ...
], 
```  

### Next

Now that your test DB schema is set, you are ready to use the factories. Let us start with [baking them](bake.md).

