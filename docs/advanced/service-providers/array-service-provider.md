# Array Service Provider

`ServiceProviderArray` is the ready-made [service provider](/advanced/service-providers/using-service-providers) that describes its services with a plain configuration array. For most cases where you want lazy service registration this is all you need — no custom class required.

It implements both `ServiceProviderInterface` and `ServiceProviderClassLookupInterface`, so it supports class lookups without instantiation out of the box.

```php
use ClanCats\Container\{
    Container,
    ServiceProviderArray
};
```

## The service configuration array

Each service is a keyed entry describing how to build it:

- **`class`** *(required)* — the fully qualified class name.
- **`arguments`** — constructor [arguments](/advanced/arguments) (`@` for a dependency, `:` for a parameter, everything else raw).
- **`shared`** — whether the resolved instance is cached as a singleton. Defaults to `true` because that is what most services want (one connection, one logger, one config). Set it to `false` when each consumer needs its own fresh instance.
- **`calls`** — method calls to run after construction, each an array with a `method` and its `arguments`.

## Using it directly

Create an instance, feed it a service map with `setServices`, then register it.

```php
$provider = new ServiceProviderArray();

$provider->setServices([
    'engine' => [
        'class' => Engine::class,
        'shared' => false,
        'calls' => [
            ['method' => 'setPower', 'arguments' => [315]],
        ],
    ],
    'producer' => [
        'class' => Producer::class,
        'arguments' => ['Audi'],
    ],
]);

$container->register($provider);
```

[~ PHPDoc](/src/ServiceProviderArray.php#setServices)

## Using it as a base class

For a fixed set of services it is often cleaner to subclass the provider and override the protected `$services` property.

```php
use ClanCats\Container\ServiceProviderArray;

class CarServiceProvider extends ServiceProviderArray
{
    protected array $services =
    [
        'car' =>
        [
            'class' => Car::class,
            'arguments' => ['@engine', '@producer'],
        ],

        'engine' =>
        [
            'class' => Engine::class,
            'shared' => false,
            'calls' => [
                ['method' => 'setPower', 'arguments' => [315]],
            ],
        ],

        'producer' =>
        [
            'class' => Producer::class,
            'arguments' => ['Audi'],
        ],
    ];
}
```

```php
$container->register(new CarServiceProvider());

$car = $container->get('car'); // built lazily, on demand
```

## How it resolves

`provides()` simply returns the keys of the service map.

[~ PHPDoc](/src/ServiceProviderArray.php#provides)

`resolve()` builds a [ServiceFactory](/advanced/service-factories) from the configuration entry via `ServiceFactory::fromArray`, constructs the service, and reports it as shared unless the entry set `shared` to `false`.

[~ PHPDoc](/src/ServiceProviderArray.php#resolve)

## Class lookups

Because the provider knows every service's `class` up front, it can answer class lookups without instantiating anything.

```php
$provider->lookupClassName('engine', $container); // Engine::class
```

An entry without a `class` key throws an `InvalidServiceException`, and an unknown service name throws an `UnknownServiceException`.

[~ PHPDoc](/src/ServiceProviderArray.php#lookupClassName)
