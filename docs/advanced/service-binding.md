# Service Binding Basics

A Container instance compiled or not, is always able to bind new or override existing services dynamically during your application runtime. This also allows us to define `Closure` callbacks as service factories.

::: tip
**Which approach should I use?** For services declared up front, prefer a `.ctn` file ([Services](/container-files/services)) — it compiles to fast PHP. Reach for `bind()` when you need to register or override a service on a *live* container at runtime. Under the hood `bind()` produces a [Service Factory](/advanced/service-factories) built from a [Service Definition](/advanced/service-definitions); use those classes directly when you want to assemble a definition programmatically.
:::

Keep in mind that in the below examples following namespaces are used: 

```php
use ClanCats\Container\{
    Container,
    ServiceFactory  
};
```

## Which bind method?

The container exposes a small family of binding methods. `bind()` is the convenient shortcut that dispatches to the others based on what you pass it; the rest are explicit and occasionally clearer.

| Method | Accepts | Shared? | Returns |
| --- | --- | --- | --- |
| `bind($name, $factory, $shared = true)` | `ServiceFactoryInterface`, `Closure`, or class-name string | via 3rd arg (default `true`) | the `ServiceFactory` **only** when given a class-name string |
| `bindClass($name, $class, $arguments = [], $shared = true)` | class-name string | via 4th arg (default `true`) | the generated `ServiceFactory` |
| `bindFactoryShared($name, $factory)` | `ServiceFactoryInterface` or `Closure` | always shared | `void` |
| `bindFactory($name, $factory)` | `ServiceFactoryInterface` or `Closure` | never shared (fresh each `get`) | `void` |

Note the naming: `bindFactory` / `bindFactoryShared` refer to a *service factory object* (a `ServiceFactoryInterface` or `Closure`), and the `Shared` suffix controls whether the result is cached. See [shared vs. factory services](#shared-vs-factory-services) below for what "shared" means.

## Bind method

The `bind` method acts as a shortcut and supports three different argument types:

 * `ServiceFactoryInterface` instance.
 * `Closure` callback.
 * Classname represented as string.

[~ PHPDoc](/src/Container.php#bind)

### Bind ServiceFactoryInterface 

The `ServiceFactoryInterface` demands only a `create` method to retrieve the expected service. This package comes with a prebuilt `ServiceFactory` class allowing you to construct a service from a `ServiceDefinition` or basically by his classname.

::: tip
Check [Service Factories](service-factories) for more.
:::

```php
$sessionFactory = new ServiceFactory('\\Acme\\Session', ['@session.provider.mysql']);

$container->bind('session', $sessionFactory);
```

### Bind a class name

When you pass a class name as a string, `bind` autogenerates a `ServiceFactory` for it and **returns that factory** so you can configure it fluently. This is the only case where `bind` returns something.

```php
$container->bind('producer', Company::class)
    ->arguments(['Massive Industries']);
```

This binds the company service under the name `producer` and adds the constructor argument `"Massive Industries"`.

```php
echo $container->get('producer')->name; // "Massive Industries"
```

### Shared vs. factory services

The optional third argument of `bind` controls whether the service is shared. It defaults to `true` (a single, cached instance). Pass `false` to make it a factory that builds a fresh instance on every request.

```php
// bind the pulsedrive engine and set the power.
// the `false` at the end marks this as a NOT shared service.
$container->bind('pulsedrive', Engine::class, false)
    ->calls('setPower', [20]);

// bind a "shuttle" spaceship, inject the pulsedrive and
// set the producer company.
$container->bind('shuttle', SpaceShip::class, false)
    ->arguments(['@pulsedrive', '@producer']);
```

Because `shuttle` is not shared, every `get` returns a new spaceship. The `producer` it references, however, was bound as shared, so both shuttles get the very same producer instance.

```php
$jumper1 = $container->get('shuttle');
$jumper2 = $container->get('shuttle');

$jumper1 === $jumper2;                     // false — a new shuttle each time
$jumper1->producer === $jumper2->producer; // true  — the shared producer
```

The `@` character tells the container to resolve a dependency — here the `pulsedrive` and `producer` services.

```php
$container->bind('malcolm', \Human::class)
    ->calls('setName', ['Reynolds']);

$container->bind('firefly', \SpaceShip::class)
    ->arguments(['@malcolm']);

echo $container->get('firefly')->ayeAye(); // aye aye captain Reynolds
```

### Bind a Closure

For construction logic that doesn't fit a plain class binding, pass a `Closure`. It receives the container and returns the service.

```php
$container->bind('config', function($c) {
    return new Config($c->get('config.loader'));
});
```

Like class-name bindings, a closure binding is shared by default; pass `false` as the third argument to make it a factory.