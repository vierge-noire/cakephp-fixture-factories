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
     * @return self
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

### Validation / Behaviors
With the aim of persisting data in the database as straighforwardly as possible, all behaviors (except Timestamp) and all validations
are deactivated when creating CakePHP entities and persisting them to the database. Validation may be reactivated / customized by overwriting
 `$marshallerOptions` and `$saveOptions` in the factory concerned.
 
 ### Namespace
 
 Assuming your application namespace in `App`, factories should be placed in the `App\Test\Factory` namespace of your application.
 Or for a plugin Foo, in `Foo\Test\Factory`.
 
 You may change that by setting in your configuration the key `TestFixtureNamespace` to the desired namespace.
 
 ### Next
 
 Let us see [how to use them](examples.md)...
