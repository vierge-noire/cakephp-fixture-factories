## Setup

Once you have installed the package via composer...

### Listeners

Make sure you replace the native CakePHP listener by the following one inside your `phpunit.xml` (or `phpunit.xml.dist`) config file, per default located in the root folder of your application:

```
<!-- Setup a listener for fixtures -->
     <listeners>
         <listener class="CakephpFixtureFactories\TestSuite\FixtureInjector">
             <arguments>
                 <object class="CakephpFixtureFactories\TestSuite\FixtureManager" />
             </arguments>
         </listener>
     </listeners>
``` 

Between each test, the package will truncate all the test tables that have been used during the previous test.

The fixtures will be created in the test database(s) defined in your [configuration](https://book.cakephp.org/4/en/development/testing.html#test-database-setup).

### Next

Learn how migrations can be used for maitaining your test DB: [Migrations](migrator.md)
