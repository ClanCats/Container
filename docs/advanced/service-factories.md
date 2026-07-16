# Service Factories

A service factory is the object that actually *builds* a service. It knows the class name, the constructor arguments and any method calls to run after construction, and it turns all of that into a concrete instance when the container asks for it.

This package ships a ready-to-use implementation, `ServiceFactory`, which implements `ServiceFactoryInterface` and extends [ServiceDefinition](/advanced/service-definitions). Because of that inheritance a factory is also a full service definition — everything you can do with a definition (`for()`, `fromArray()`, `arguments()`, `calls()`, the `add*Argument` helpers) is available on a factory too.

```php
use ClanCats\Container\{
    Container,
    ServiceFactory
};
```

## The factory interface

`ServiceFactoryInterface` demands a single method: `create`, which receives the container and returns the built service.

```php
interface ServiceFactoryInterface
{
    public function create(Container $container);
}
```

The `ServiceFactory::create` implementation resolves the constructor [arguments](/advanced/arguments), instantiates the class and then applies every registered method call.

[~ PHPDoc](/src/ServiceFactory.php#create)

## Constructing a factory

The constructor takes the class name and an optional array of constructor arguments. Prefixing a string with `@` marks it as a dependency, `:` marks a parameter.

```php
$sessionFactory = new ServiceFactory('\\Acme\\Session', ['@session.provider.mysql']);
```

You can keep configuring it using the inherited definition methods:

```php
$engineFactory = ServiceFactory::for(Engine::class)
    ->calls('setPower', [315]);
```

::: tip
Read [Service Arguments](/advanced/arguments) for the full argument syntax and [Service Definitions](/advanced/service-definitions) for every builder method a factory inherits.
:::

### Building from an array

Because `ServiceFactory` extends `ServiceDefinition`, you can build one from a plain configuration array with `fromArray`. The `class` key is required; `arguments` and `calls` are optional.

```php
$factory = ServiceFactory::fromArray([
    'class' => Car::class,
    'arguments' => ['@engine', '@producer'],
    'calls' => [
        ['method' => 'setProducer', 'arguments' => ['@producer']],
    ],
]);
```

[~ PHPDoc](/src/ServiceDefinition.php#fromArray)

## Binding a factory to the container

The most common way to register a factory is through `bind`, whose third argument decides whether the resulting service is shared (default) or a fresh instance every time.

```php
// shared (singleton) — the default
$container->bind('session', $sessionFactory);

// factory (a new instance on every get) — pass false
$container->bind('engine', $engineFactory, false);
```

If you already have a factory instance and want to be explicit about the sharing behaviour, use the dedicated methods instead of the `bind` shortcut.

### Bind a shared factory

```php
$container->bindFactoryShared('session', $sessionFactory);
```

[~ PHPDoc](/src/Container.php#bindFactoryShared)

### Bind an unshared factory

```php
$container->bindFactory('engine', $engineFactory);
```

[~ PHPDoc](/src/Container.php#bindFactory)

::: tip
For the full picture on the different ways to bind services (class names, closures, factory instances) see [Service Binding](/advanced/service-binding).
:::
