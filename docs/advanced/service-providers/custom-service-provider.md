# Custom Service Provider

When building a service needs real logic — reading a file, branching on configuration, wiring up a family of related objects — a static array is not enough. In those cases you write your own [service provider](/advanced/service-providers/using-service-providers) by implementing `ServiceProviderInterface`.

```php
use ClanCats\Container\{
    Container,
    ServiceProviderInterface
};
```

## Implementing the interface

The interface requires two methods: `provides()`, listing the names you can build, and `resolve()`, which builds one of them.

```php
class VehicleServiceProvider implements ServiceProviderInterface
{
    public function provides() : array
    {
        return ['engine', 'producer'];
    }

    public function resolve(string $serviceName, Container $container) : array
    {
        switch ($serviceName)
        {
            case 'engine':
                $engine = new Engine();
                $engine->setPower(315);

                // return the instance and mark it as a factory (not shared)
                return [$engine, false];

            case 'producer':
                // shared: the container caches and reuses this instance
                return [new Producer('Audi'), true];
        }

        throw new \ClanCats\Container\Exceptions\UnknownServiceException(
            'The provider cannot resolve the service "' . $serviceName . '".'
        );
    }
}
```

### The shared return value

`resolve()` returns a two-element array `[$service, $shared]`. The boolean is important:

- Return `true` and the container caches the instance — every later `get` yields the same object.
- Return `false` and the container calls `resolve()` again on every request, giving a fresh instance each time.

## Registering it

```php
$container->register(new VehicleServiceProvider());

$container->get('producer'); // resolved lazily through the provider
```

[~ PHPDoc](/src/Container.php#register)

## Adding class lookups

If you want the container to be able to report a service's class without instantiating it (used by `getClassForService`), also implement `ServiceProviderClassLookupInterface`.

```php
use ClanCats\Container\{
    Container,
    ServiceProviderInterface,
    ServiceProviderClassLookupInterface
};

class VehicleServiceProvider implements ServiceProviderInterface, ServiceProviderClassLookupInterface
{
    public function provides() : array { /* ... */ }

    public function resolve(string $serviceName, Container $container) : array { /* ... */ }

    public function lookupClassName(string $serviceName, Container $container) : string
    {
        switch ($serviceName)
        {
            case 'engine':   return Engine::class;
            case 'producer': return Producer::class;
        }

        throw new \ClanCats\Container\Exceptions\UnknownServiceException(
            'The provider cannot resolve the service "' . $serviceName . '".'
        );
    }
}
```

::: tip
If your services can be expressed as a simple configuration map, you usually don't need a custom provider at all — the [Array Service Provider](/advanced/service-providers/array-service-provider) already implements all of this for you.
:::
