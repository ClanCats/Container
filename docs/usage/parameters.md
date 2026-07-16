# Parameters

Parameters are the container's storage for configuration values. Think of them as the scalar knobs of your application — hostnames, secrets, timeouts, feature flags. There is no technical limitation on what a parameter may hold (you *can* store an object), but conceptually you should keep them to values, not services.

::: tip
This page covers the runtime PHP API. If you are looking for how to declare parameters inside a `.ctn` container file, read [Container Files › Parameters](/container-files/parameters).
:::

## Initial parameters

The container constructor accepts an array of initial parameters:

```php
use ClanCats\Container\Container;

$container = new Container(['env' => 'production']);
```

These initial values are **merged** with any parameters already defined (for example the ones baked into a compiled container). This makes the constructor a convenient place to inject environment specific overrides — see [Basic Container usage](/usage/container) for the equivalent factory call.

The same array can be handed to the factory when you build a container:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');
$container = $factory->create('AppContainer', function($builder) {}, ['env' => 'production']);
```

## Reading a parameter

```php
$container->getParameter('env');
```

You may supply a fallback that is returned when the parameter has **not** been defined. Note that a parameter explicitly set to `null` still counts as defined, so the fallback will *not* kick in for it.

```php
$container->getParameter('cron.idle.timeout', 3600);
```

[~ PHPDoc](/src/Container.php#getParameter)

## Setting a parameter

Parameters can be written at any point during runtime.

```php
$container->setParameter('session.cookie.name', 'mysessiontoken');
```

The value is not limited to strings — arrays and other structures are fine too.

```php
$container->setParameter('available.languages', ['de', 'en', 'it', 'fr']);
```

[~ PHPDoc](/src/Container.php#setParameter)

## Checking if a parameter exists

Sometimes you just need to know whether a parameter is present. Remember: a parameter whose value is `null` still exists.

```php
if ($container->hasParameter('smtp.host')) {
    // ...
}
```

[~ PHPDoc](/src/Container.php#hasParameter)

## Reading all parameters

For debugging and introspection you can dump the full parameter map.

```php
foreach ($container->allParameters() as $name => $value) {
    // ...
}
```

[~ PHPDoc](/src/Container.php#allParameters)
