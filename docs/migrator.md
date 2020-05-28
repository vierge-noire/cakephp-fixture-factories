## The Migrator

The traditional CakePHP fixtures both create the schema of your test DBs and populate them with fixtures. The present package only cares about populating the test DBs on the fly.

### Default migrations  

The package proposes a tool to run your [migrations](https://book.cakephp.org/migrations/3/en/index.html) once prior to the tests. In order to do so,
you may place the following in your `tests/bootstrap.php`:
```$xslt
\CakephpFixtureFactories\TestSuite\Migrator::migrate();
```
This command will ensure that your migrations are well run and keeps the test DB(s) up to date.

### Multiple migrations settings

Should you have migrations at different places or connections than the default ones, you can configure these by creating a `config/fixture_factories.php` file similar to the following:
```$xslt
<?php

return [   
    'TestFixtureMigrations' => [
        ['connection' => 'test'],       // this is the default migration configuration that you now have to include, if needed
        ['plugin' => 'FooPlugin'],      // these are the migrations of the FooPlugin
        ['source' => 'BarFolder']       // these are the migrations located in a BarFolder
    ],
];
```

Alternatively, you can also pass the various migrations directly in the `Migrator` instanciation:
```$xslt
\CakephpFixtureFactories\TestSuite\Migrator::migrate([
     ['connection' => 'test'],       
     ['plugin' => 'FooPlugin'],      
     ['source' => 'BarFolder']
 ]);
```

If you ever switched to a branch with different migrations, the `Migrator` will automatically drop the tables where needed, and re-run the migrations. Switching branches therefore
does not require any manipulation on your side.

### Next

Now that your test DB schema is set, you are ready to use the factories. Let us start with [baking them](bake.md).

