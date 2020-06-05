## Bake command

We recommand you to use the bake command in order create your factories.

### Load the plugin

Make sure to add:
```
$this->addPlugin('CakephpFixtureFactories');
```

in the `bootstrap` method of your `Application.php`. See here for more details on [how to load a Plugin](https://book.cakephp.org/4/en/plugins.html#loading-a-plugin).

### The command

The command
```
bin/cake bake fixture_factory -h
```
will assist you. You have the possiblity to bake factories for all (`-a`) your models. You may also include help methods (`-m`)
based on the associations defined in your models. Factories can be baked within plugin with the command `-p`.

### Next

Let us now see how the factories [look](factories.md)...

