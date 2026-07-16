# Default Implementation

For most applications you'll want the fastest possible container, and this is how you get it. The recommended setup combines three pieces — the factory, the builder and a namespace — to generate an optimized PHP class containing your whole service graph, include it, and hand you back a ready instance.

The payoff is that the expensive work — parsing your `.ctn` files and resolving the dependency graph — happens once, at build time. Every request after that simply includes a plain generated class, so there's almost nothing left to do at runtime. See [The Container Lifecycle](/usage/lifecycle) for the full picture of what compilation does.

The generated class is written to a cache directory, so make sure the directory you hand to the factory exists and is writable by PHP.

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder)
{
    // create a new container file namespace and parse our `app.ctn` file.
    $namespace = new \ClanCats\Container\ContainerNamespace();
    $namespace->parse(__DIR__ . '/app.ctn');

    // import the namespace data into the builder
    $builder->importNamespace($namespace);
});
```

## Debug mode

The container factory does not watch your files for changes — it only rebuilds when the cache file is missing. That's ideal in production, but during development it means your edits to a `.ctn` file wouldn't take effect until you cleared the cache by hand. To save you that chore, **debug mode** ignores the existing cache file and rebuilds on every call:

```php
use \ClanCats\Container\ContainerFactory;
$factory = new ContainerFactory(__DIR__ . '/cache', true); // second argument turns debug mode on
```

## Container class name

The first argument you pass to the factory's `create` method is the container's class name, and you may include a namespace so the generated class lands wherever you like.

```php
$container = $factory->create('Acme\DI\AppContainer', function($builder) {});
```

Read more about building containers in the [Getting started](/introduction/getting-started) guide.

## Container namespace

::: tip
Continue with [Container Files › Parsing](/container-files/parsing) to learn how a `ContainerNamespace` loads and imports `.ctn` files.
:::
