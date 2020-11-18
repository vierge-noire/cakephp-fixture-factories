## Setup

Once you have installed the package via composer...

### Listeners

Make sure you *replace* the native CakePHP listener by the following one inside your `phpunit.xml` (or `phpunit.xml.dist`) config file,
per default located in the root folder of your application:

```
<!-- Setup a listener for fixtures -->
     <listeners>
         <listener class="CakephpTestSuiteLight\FixtureInjector">
             <arguments>
                 <object class="CakephpTestSuiteLight\FixtureManager" />
             </arguments>
         </listener>
     </listeners>
``` 

The following command will do that for you.

```
bin/cake fixture_factories_setup
```

You can specify a plugin (`-p`) and a specific file (`-f`), if different from `phpunit.xml.dist`.

Between each test, the package will truncate all the test tables that have been used during the previous test.

The fixtures will be created in the test database(s) as defined in your [configuration](https://book.cakephp.org/4/en/development/testing.html#test-database-setup).

### Ignoring connections

The package will empty the tables found in all test databases. If you wish to ignore a given connection, you may create a 
`config/test_suite_light.php` file and provide the connections that should be ignored:

```$xslt
<?php

return [   
    'TestSuiteLightIgnoredConnections' => [
        'test_foo_connection_to_be_ignored',
        'test_bar_connection_to_be_ignored',
        ...
    ],
];
```

This can be useful for example if you have connections to a third party server in the cloud that should be ignored.

### Next

Learn how migrations can be used for maintaining your test DB: [Migrations](migrator.md)
