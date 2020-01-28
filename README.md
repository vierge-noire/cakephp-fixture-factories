# test-fixture-factories

This package provides an alternative way of managing fixtures in a cakephp application. 
The main idea is to provide fixture factories in replacement to the fixtures you can find out of the box in cakephp
Using factories for managing fixtures has many advantages in terms of maintenance and readability inside your tests.

It is composed of the following classes
* BaseFactory
* FixtureInjector, which implements phpunit's BaseTestListener interface
* FixtureManager, which extends cakephp's FixtureManager class

### Installation

Add the bprojects gitlab private repository to you composer.json repositories array

```
    "repositories": [
        {
            "type": "vcs",
            "url": "git@gitlab.com:b-projects-main/test-fixture-factories.git"
        }
    ],

```

Add the package to your require-dev list
```
composer require-dev bprojects/test-fixture-factories
```

Make sure you inject the fixture manager inside your phpunit xml config file

```
<!-- Setup a listener for fixtures -->
     <listeners>
         <listener class="\Core\Test\TestSuite\FixtureInjector">
             <arguments>
                 <object class="\Core\Test\TestSuite\FixtureManager" />
             </arguments>
         </listener>
     </listeners>
``` 

###How it works

Between each test, The fixture manager will truncate all the test tables that have been used during the previous test

All the test tables are truncated before each test.