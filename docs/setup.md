## Setup

Once you have installed the package via composer, the test suite light should be
configured. This section describes the basic steps to take. 
You will find the official documentation for the test suite light package [here](https://github.com/vierge-noire/cakephp-test-suite-light#cakephp-test-suite-light).

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

### Next

Learn how migrations can be used for maintaining your test DB: [Migrations](https://github.com/vierge-noire/cakephp-test-migrator)
