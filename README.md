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

###BaseFactory

The BaseFactory class relies behind the scene on the Marshaller to create entities from arrays.
Have a look at the cakephp doc below to get a an overview on how to define associated data in your array.
It is important to understand this in order to know how to define your data arrays

https://book.cakephp.org/3.next/en/orm/saving-data.html#converting-request-data-into-entities

###Running the tests

Create a database called 'test_fixture_factories' like described in the tests/bootstrap.php.
To access the database, make sure the following credentials are working
Username should be : root
Password should be : vagrant

The migrations are run eaach time the Applicatio bootstraps so you shouldn't worry about that.
