# Services

A service is any object the container knows how to construct for you. Whether the container was compiled from a `.ctn` file, built with the [ContainerBuilder](/usage/implementations/custom-build) or wired up dynamically at runtime, the API for resolving services is always the same.

## Shared vs. factory services

Every service is resolved in one of two ways:

 * **Shared** (the default) — the service is constructed once, cached, and the very same instance is returned on every subsequent `get`. This is the singleton behaviour most services want.
 * **Factory** (prototype) — a fresh instance is constructed on every `get`.

How a service becomes shared or not is a matter of *binding* it, which is covered in [Service Binding](/advanced/service-binding). This page is about resolving and managing services that already exist.

## Getting a service

Retrieves the service with the given name. If the service is shared and has not been requested before, it is constructed now and cached; factory services are constructed fresh every time.

```php
$container->get('repository.comment');
```

The name `container` is reserved and always refers to the container itself:

```php
$container->get('container'); // === $container
```

Requesting a service that does not exist throws an `UnknownServiceException`. Either guard the call with `has()` or catch the exception if you don't fully trust your dependency tree.

```php
try {
    $container->get('not.sure.if.exists');
} catch (\ClanCats\Container\Exceptions\UnknownServiceException $e) {
    // it's not there
}
```

[~ PHPDoc](/src/Container.php#get)

## Checking if a service exists

```php
if ($container->has('not.sure.if.exists')) {
    $container->get('not.sure.if.exists');
}
```

[~ PHPDoc](/src/Container.php#has)

## Listing available services

Useful when building debugging tooling — returns the names of every service the container can resolve.

```php
echo implode(', ', $container->available());
```

[~ PHPDoc](/src/Container.php#available)

## Checking if a service is resolved

Tells you whether a service has already been constructed / requested. Mainly handy for profiling and debugging.

```php
foreach ($container->available() as $serviceName) {
    echo "@{$serviceName}: " . ($container->isResolved($serviceName) ? 'yes' : 'no') . PHP_EOL;
}
```

[~ PHPDoc](/src/Container.php#isResolved)

## Looking up services by metadata (tags)

Services can carry arbitrary metadata declared in a `.ctn` file with the `=` prefix (see [Metadata & Tags](/container-files/metadata)). At runtime you collect them by key — this is how you gather, for example, every controller that declared a `route` without listing them by hand.

`serviceNamesWithMetaData()` returns a map of service name to that service's entries for the given key:

```php
foreach ($container->serviceNamesWithMetaData('route') as $serviceName => $entries) {
    $controller = $container->get($serviceName);

    foreach ($entries as $route) {
        // $route is one `= route: ...` entry, e.g. [['GET', 'POST'], '/signin']
        $router->add($route[0], $route[1], $controller);
    }
}
```

[~ PHPDoc](/src/Container.php#serviceNamesWithMetaData)

You can also read the metadata of a single service, or list which keys it carries:

```php
$container->getMetaData('controller.auth.sign_in', 'route'); // the 'route' entries
$container->getMetaDataKeys('controller.auth.sign_in');      // e.g. ['route', 'tag']
```

[~ PHPDoc](/src/Container.php#getMetaData)

[~ PHPDoc](/src/Container.php#getMetaDataKeys)

## Injecting an existing instance

When you already have an object and simply want to hand it to the container, use `set`. This stores the instance directly and overwrites anything shared under the same name.

```php
$thing = new Thing();

$container->set('thing', $thing);
```

[~ PHPDoc](/src/Container.php#set)

::: tip
To register a service *definition* (so the container constructs it lazily on demand) rather than a ready-made instance, use `bind`. See [Service Binding](/advanced/service-binding).
:::

## Releasing a service

Removes the shared instance stored in the container without touching the service definition — the next `get` will construct a fresh one. Keep in mind PHP objects are reference counted, so an instance you still hold a reference to elsewhere is not actually destroyed.

```php
$container->release('db.connection');
```

[~ PHPDoc](/src/Container.php#release)

## Removing a service

Releases the resolved instance (if any) *and* removes the service definition entirely.

```php
$container->remove('kernel');
```

::: tip
This also works for compiled service definitions.
:::

[~ PHPDoc](/src/Container.php#remove)
