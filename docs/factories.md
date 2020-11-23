## Fixture Factories

### What they look like

A factory is a class that extends the `CakephpFixtureFactories\Factory\BaseFactory`. It should implement the following two methods:
* `getRootTableRegistryName()`  which indicates the model that the factory will use to buld its fixtures;
* `setDefaultTemplate()`  which sets the default configuration of each entity created by the factory.

The `Faker\Generator` is used in order to randomly populate fields, and is anytime available using `$this->getFaker()`.

[Here is further documentation on Fake](https://github.com/fzaninotto/Faker). 

Let us consider for example a model `Articles`, related to multiple `Authors`.

This could be for example the `ArticleFactory`. Per default the fields `title` and `body` are set with `Faker` and two associated `authors` are created.
```$xslt
namespace App\Test\Factory;

use CakephpFixtureFactories\Factory\BaseFactory;
use Faker\Generator;

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
     * @return void
     */
    protected function setDefaultTemplate()
    {
          $this->setDefaultData(function(Generator $faker) {
               return [
                    'title' => $faker->text(30),
                    'body'  => $faker->text(1000),
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
You may add any methods related to your business model, such as `setJobTitle` to help you build efficient and reusable factories.

### Required fields

If a field is required in the database, it will have to be populated in the `setDefaultTemplate` method. You may simply set it to a fixed value, for example 1.

### Validation / Behaviors

With the aim of persisting data in the database as straighforwardly as possible, all validations and rules
are deactivated when creating CakePHP entities and persisting them to the database. Validationa nd rules may be reactivated / customized by overwriting
the properties `$marshallerOptions` and `$saveOptions` in the factory concerned.
 
### Model events and behaviors

Per default, *all model events* of a factory's root table and their behaviors are switched off *except those of the timestamp behavior*.

The intention is to create fixtures as fast and transparently as possible without interfering with the business model.

#### Model events

Is is however possible to activate an event model with the method `listeningToModelEvents`.

This can be made on the fly:
```$xslt
$article = ArticleFactory::make()->listeningToModelEvents('Model.beforeMarshal')->getEntity();
```
or per default in the factory's `setDefaultTemplate` method:
```$xslt
protected function setDefaultTemplate()
{
      $this->setDefaultData(function(Generator $faker) {
           return [
                'title' => $faker->text(30),
                'body'  => $faker->text(1000),
           ];
      })
      ->withAuthors(2)
      ->listeningToModelEvents([
        'Model.beforeMarshal',
        'Model.beforeSave',
      ]);
}
```

Note that you can provide either a single event, or an array of events. You will find a list of all model events [here](https://book.cakephp.org/4/en/orm/table-objects.html#event-list).

#### Behavior events

It is possible to activate the model events of a behavior in the same way with the method `listeningToBehaviors`.

This can be made on the fly:
```
$article = ArticleFactory::make()->listeningToBehaviors('Sluggable')->getEntity();
```
or per default in the factory's `setDefaultTemplate` method.

Additionaly, you can declare a behavior globaly. This can be useful for behaviors that impact a large amount of tables
and for which not nullable fields need to be populated.

You may create a `fixture_factories.php` config file in your application's `config` folder. Under the key `TestFixtureGlobalBehaviors`, you will need to define all the behaviors that will be listened to, provided that the root table itself is listening to them.

```$xslt
'TestFixtureGlobalBehaviors' => [
        'SomeBehaviorUsedInMultipleTables',
    ],
```

Note that even if the behavior is located in a plugin, you should, according to CakePHP conventions, provide the name of the behavior only. Provide `BehaviorName` and not `SomeVendor/WithPluginName.BehaviorName`.

The static method `makeWithModelEvents` is now deprecated and will be removed soon.
 
### Namespace
 
Assuming your application namespace in `App`, factories should be placed in the `App\Test\Factory` namespace of your application.
Or for a plugin Foo, in `Foo\Test\Factory`.
 
You may change that by setting in your configuration the key `TestFixtureNamespace` to the desired namespace.
 
### Next
 
Let us now see [how to use them](examples.md)...
