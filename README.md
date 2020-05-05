# CakePHP Fixture Factories

This package provides an alternative approach of managing test fixtures in a [CakePHP](https://book.cakephp.org/4/en/development/testing.html) application. 
The main idea is to provide fixture factories in replacement to the fixtures you can find out of the box in cakephp.
Using factories for managing fixtures has many advantages in terms of maintenance, test performance and readability inside your tests.

It is mainly composed of the following classes
* BaseFactory
* FixtureInjector, which implements phpunit's BaseTestListener interface
* FixtureManager, which extends CakePHP's FixtureManager class
* FixtureFactoryCommand to assist you baking your model factories 

## Installation

```
composer require --dev pakacuda/cakephp-fixture-factories
```

Make sure you inject the fixture manager inside your `phpunit.xml` config file, per default located in the root folder of your application:

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
The package will not create any schema for the test database. It is therefore highly recommanded that you use
migrations to generate the test database, ideally within `./tests/bootstrap.php`.

The fixtures will be created in the test database as defined in your [configuration](https://book.cakephp.org/4/en/development/testing.html#test-database-setup).

Afther the modifications above, the traditional [CakePHP test fixtures](https://book.cakephp.org/4/en/development/testing.html#fixtures) will be ignored.

[Here is a presentation](https://www.youtube.com/watch?v=a7EQvHkIb60&t=107m54s) held at the CakePHP online Meetup on 29th April 2020

## Bulding factories

### Bake command

We recommand you to use the bake command in order prepare your factories. In order to do so, simply load the `CakephpFixtureFactories` plugin 
by adding `$this->addPlugin('CakephpFixtureFactories');` in your `Application.php` bootstrap method, idealy right after loading the `Bake` plugin.

The command
```
bin/cake fixture_factory -h
```
will assist you. You have the possiblity to bake factories for all (`-a`) your models. You may also include building methods (`-m`)
based on the associations defined in your models.

### Factory
A factory is a class that extends the `CakephpFixtureFactories\Factory`. It should implement the following two methods:
* `getRootTableRegistryName()`  which indicates the model that the factory will use to buld its fixtures;
* `setDefaultTemplate()`  which sets the default configuration of each entity created by the factory.

The Faker\Generator class is used in order to randomly populate fields, and is anytime available using `$this->getFaker`.

[Here is further documentation on Fake](https://github.com/fzaninotto/Faker). 

Let us consider for example a model Articles, related to multiple Authors, while each author has an address in the model Addresses.

This could be for example the ArticleFactory, with a random title and content and two authors per default.
```$xslt
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;

class ArticleFactory extends BaseFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return "Articles";
    }

    /**
     * Defines the default values of you factory. Useful for
     * not nullable fields.
     * Use the patchData method to set the field values.
     * You may use methods of the factory here
     * @return self
     */
    protected function setDefaultTemplate()
    {
          $this->setDefaultData(function(Generator $faker) {
               return [
                    'title'   => $faker->text(30),
                    'content' => $faker->text(1000),
               ];
          })
          ->withAuthors(2);
    }

    public function withAuthors($parameter = null, int $n = 1): self
    {
        return $this->with('Authors', AuthorFactory::make($parameter, $n));
    }

    /**
     * Set the Article's title as a random job title     
     * @return ArticleFactory
     */
    public function setJobTitle()
    {
        return $this->patchData([
            'title' => $this->getFaker()->jobTitle,
        ]);
    }
}
```
You may add any methods to help you build efficient and reusable test features.

## Creating test features

### Validation / Behaviors
With the aim of persisting data in the database as simply as possible, all behaviors (except Timestamp) and all validations
are deactivated when creating CakePHP entities and persisting them to the database. Validation may be reactivated / customized by overwriting
 `$marshallerOptions` and `$saveOptions` in the factory concerned.

### Static features

One article with a random title, as defined in the factory above:
```
$article = ArticleFactory::make()->getEntity();
``` 
Two articles with different random titles:
```
$articles = ArticleFactory::make(2)->getEntities();
``` 
One article with title set to 'Foo'
```
$article = ArticleFactory::make(['title' => 'Foo'])->getEntity();
``` 
Three articles with the title set to 'Foo'
```
$articles = ArticleFactory::make(['title' => 'Foo'], 3)->getEntities();
``` 

In order to persist the data generated, use the method `persist` instead of `getEntity` resp. `getEntities`:
```
$articles = ArticleFactory::make(3)->persist();
```

### Dynamic features
The drawback of the previous example, is that, if you haven't defined the `title` field with `faker` in the `setDefaultTemplate` method,  all the generated examples have the same title. The following
generates three articles with different random titles:
```
use App\Test\Factory\ArticleFactory;
use Faker\Generator;
...
$articles = ArticleFactory::make(function(ArticleFactory $factory, Generator $faker) {
   return [
       'title' => $faker->text,
   ];
}, 3)->persist();
```

### Chaining methods
The aim of the test fixture factories is to bring business coherence in your test fixtures.
This can be simply achieved using the chainable methods of your factories. As long as those return `$this`, you may chain as much methods as you require.
In the following example, we make use of a method in the Article factory in order to easily create articles with a job title.
It is a simple study case, but this could be any pattern of your business logic. 
```
$articleFactory = ArticleFactory::make(['title' => 'Foo']);
$articleFoo1 = $articleFactory->persist();
$articleFoo2 = $articleFactory->persist();
$articleJobOffer = $articleFactory->setJobTitle()->persist();
```
 
 The two first articles have a title set two 'Foo'. The third one has a job title, which is randomly generated by fake, as defined n the
 ArticleFactory. 
 
 ### Associations
 If you have baked your factories with the option `-m` or `--methods`, you will have noticed that a method for each association
 has been inserted in the factories. This will assist you creating fixtures for the associated models. For example, we can 
 create an article with 10 authors as follow:
 ```
use App\Test\Factory\ArticleFactory;
use App\Test\Factory\AuthorFactory;
use Faker\Generator;
...
 $article = ArticleFactory::make()->with('authors', AuthorFactory::make(10))->persist();
```
or using the method defined in our Articlefactory:
```
$article = ArticleFactory::make()->withAuthors(10)->persist();
```

If we wish to randomly populate the field `biography` of the 10 authors of our article, with 10 different biographies:
```
$article = ArticleFactory::make()->withAuthors(function(AuthorFactory $factory, Generator $faker) {
    return [
        'biography' => $faker->realText()
    ];
}, 10)->persist();
```

## Authors
Nicolas Masson
Juan Pablo Ramìrez 
