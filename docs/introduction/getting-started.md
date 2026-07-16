# Getting Started

Welcome! This is the guided, take-your-time companion to the Quick Start in the [README](https://github.com/ClanCats/Container/blob/master/README.md). Together we'll build a small working container from scratch, one piece at a time — so don't worry if not everything clicks straight away. It will by the end.

By the time we're done you'll have written your first `.ctn` container file, wired up a couple of real PHP classes, and pulled a fully-assembled object back out of the container with a single `get()` call.

If dependency injection is new to you, it's worth reading [Core Concepts](/introduction/core-concepts) first, and [Overview](/introduction/overview) gives an at-a-glance picture of how the pieces fit together. And if you haven't installed the package yet, start with [Installation](/introduction/installation) — it's a single `composer require`.

[[toc]]

## What we're building

We'll wire up a tiny spaceship: an `Engine` service that needs a mechanic, and a `Human` who plays that mechanic. Along the way we'll cover parameters, splitting configuration into its own file, importing it, and finally the services themselves.

Here's the directory structure we're working toward:

```
app.php       # bootstraps and uses the container
app.ctn       # our main container file
config.ctn    # a separate configuration file
composer.json
cache/        # generated container class lands here — make sure it's writable
src/
  Human.php
  Engine.php
```

Throughout this guide we'll use the recommended **Default** implementation, which compiles your `.ctn` files into a fast generated PHP class. That's almost always the right choice; if you're curious about the alternatives (dynamic, custom build), see [Choosing an Implementation](/usage/implementations/choosing) — but there's no need to decide anything now.

## Wiring up the container

Before we can write any services, we need a container to put them in. That's the job of the `ContainerFactory`: it takes your `.ctn` files, compiles them into a plain PHP class, and hands you a ready-to-use container instance.

Pop this into `app.php`:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder)
{
    // create a container namespace and parse our `app.ctn` file
    $namespace = new \ClanCats\Container\ContainerNamespace([
        'config' => __DIR__ . '/config.ctn',
    ]);
    $namespace->parse(__DIR__ . '/app.ctn');

    // import the parsed definitions into the builder
    $builder->importNamespace($namespace);
});
```

And that's it — from here on, whatever we write in `app.ctn` shapes the container we get back.

### What each line does

Let's slow down and walk through that snippet, so nothing feels like magic.

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');
```

The factory has a single, focused job: to write and read the generated PHP class in the given cache directory. You may switch on its _debug mode_ by passing `true` as the second argument — in debug mode the factory ignores anything it has already built and rebuilds every time, which is exactly what you want while editing `.ctn` files. We'll come back to this at the end.

```php
$container = $factory->create('AppContainer', function($builder)
```

The `create` method is where the container is actually built. The first argument is the class name to generate; namespaces are supported, so you're free to use something like `Acme\MainBundle\MainContainer`. The second argument is a callback where we describe what the container should contain.

```php
$namespace = new \ClanCats\Container\ContainerNamespace([
    'config' => __DIR__ . '/config.ctn',
]);
```

So what is a container namespace? Think of it as a little application with its own file structure, which the namespace defines. `config` here isn't a special key — it's simply a name we assign to a file, so we can refer to it later from inside our container files.

```php
$namespace->parse(__DIR__ . '/app.ctn');
```

Now we parse the main file. All the services and parameters it declares are read and attached to our namespace instance. You can read more in [Container Files › Parsing](/container-files/parsing).

```php
$builder->importNamespace($namespace);
```

Finally, we feed the namespace into the builder, which turns those declarations into the generated container class.

::: tip
The [Default Implementation](/usage/implementations/default) guide covers the factory and builder in more depth. There's also a `tmLanguage` grammar available for `.ctn` syntax highlighting in your editor.
:::

## Defining your first parameter

With the plumbing in place, let's write something into `app.ctn`. The simplest thing a container file can hold is a **parameter** — a plain configuration value.

Parameters are always prefixed with a `:` and can be defined in any order. Inside a `.ctn` file they may hold scalar values and arrays:

```ctn
:firstname: 'James'
:lastname: 'Bond'
:code: 007
```

You can read a parameter back off the container at any time:

```php
echo $container->getParameter('firstname'); // James
```

::: tip
The container itself puts no limit on what a parameter can hold — you're free to set a parameter to anything you like from PHP with the `setParameter` method. See [Parameters](/usage/parameters).
:::

## Splitting configuration into its own file

You may have noticed we told the namespace about *two* `.ctn` files. As an application grows, it's tidy to keep configuration separate from your service definitions. Let's put a parameter in `config.ctn`:

```ctn
:missions.available: {
    'Goldeneye',
    'Goldfinger',
}
```

If we tried to read `missions.available` right now, we'd get `null` — because `config.ctn` isn't part of our container yet. We need to **import** it into `app.ctn` first, which is a one-liner:

```ctn
import config

:firstname: 'James'
:lastname: 'Bond'
:code: 007
```

Remember where we built the namespace? That's where we gave `config.ctn` the name `config` — and `config` is exactly the name we import here.

On its own this example looks trivial, but the payoff shows up at scale: keeping configuration in its own file, apart from your service definitions, keeps both readable — and imports stitch them neatly back together. There's more on this in [Imports & Overriding](/container-files/imports).

## Defining a service

Parameters are the warm-up. The real reason the container exists is to build and wire your objects for you — your **services**.

Before we can define one, we need a class to define. Our first is an `Engine`. It has nothing to do with the container itself; it's just here to give us something real to wire up. It's constructed with a given power and amount of fuel, `throttle` burns fuel to travel a distance, `refuel` tops it back up (but only with a mechanic on board), and `setMechanic` assigns that mechanic.

Save this as `src/Engine.php`:

```php
class Engine
{
    protected $fuel;
    protected $power;
    protected $mechanic;

    public function __construct(int $power, int $fuel) {
        $this->power = $power;
        $this->fuel = $fuel;
    }

    public function throttle(int $for) : int {
        $this->fuel -= ($distance = $this->power * $for); return $distance;
    }

    public function getFuel() : int {
        return $this->fuel;
    }

    public function refuel(int $amount) {
        if ($this->mechanic) $this->fuel += $amount;
    }

    public function setMechanic(Human $mechanic) {
        $this->mechanic = $mechanic;
    }
}
```

Now we can declare it as a service in `app.ctn`. Services are prefixed with `@`, followed by the class name and its constructor arguments:

```ctn
@hyperdrive: Engine(500, 10000)
```

That's the whole definition. We can now load the hyperdrive from the container and take it for a spin:

```php
$hyperdrive = $container->get('hyperdrive');

echo 'current fuel: ' . $hyperdrive->getFuel() . PHP_EOL; // 10000
echo 'traveling: ' . $hyperdrive->throttle(5) . PHP_EOL;  // 2500
echo 'current fuel: ' . $hyperdrive->getFuel() . PHP_EOL; // 7500
```

### Using parameters as arguments

Often you won't want to hardcode constructor arguments right there in the definition. This is where parameters come in handy — reference one with its leading `:`:

```ctn
:hyperdrive.power: 500
:hyperdrive.fuel: 10000

@hyperdrive: Engine(:hyperdrive.power, :hyperdrive.fuel)
```

The engine behaves exactly as before, but its configuration now lives in named parameters you can tweak in one place. But our engine still can't refuel without a mechanic — which brings us neatly to the next class.

## Injecting one service into another

Our second class is a `Human` with a single constructor argument, their name, and a job you can set afterwards.

Save this as `src/Human.php`:

```php
class Human
{
    public $name;
    public $job;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function setJob(string $job) {
        $this->job = $job;
    }
}
```

Here comes our mechanic. Notice the `-` line: that's a **method call**, run on the object right after it's constructed.

```ctn
@kaylee: Human('Kaylee Frye')
  - setJob('Mechanic')
```

(Yes, the names are a nod to *Firefly* — bear with the references.)

Now for the part that makes a container a container: one service can depend on another. To hand Kaylee to the engine, we reference her service with `@kaylee` — the same `@` prefix we use to declare a service, now used to *point at* one:

```ctn
@hyperdrive: Engine(:hyperdrive.power, :hyperdrive.fuel)
  - setMechanic(@kaylee)
```

When you ask for `hyperdrive`, the container sees it needs `kaylee`, builds her first (job and all), and injects her — no wiring by hand. And voilà, we can refuel:

```php
$hyperdrive = $container->get('hyperdrive');

$hyperdrive->throttle(5);
$hyperdrive->refuel(1000);
echo 'current fuel: ' . $hyperdrive->getFuel() . PHP_EOL; // 8500
```

## What just happened

You've just built a real, compiled container. The first time you ran `app.php`, the `ContainerFactory` parsed your `.ctn` files, resolved the dependency graph, and wrote a compiled `AppContainer` class into the `cache/` directory. Every request after that simply includes that generated class — no parsing, near-zero overhead.

There's one catch worth knowing early: because the compiled class is cached, editing your `.ctn` files won't take effect until the cache is rebuilt. While developing, enable **debug mode** (the second argument we mentioned earlier) so every change is picked up automatically:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache', true);
```

## Where to go next

You now know enough to start wiring up your own application. When you're ready to go deeper:

 * **[The Container Lifecycle](/usage/lifecycle)** — what compiling actually does, and when each step runs.
 * **[Container Files](/container-files/introduction)** — the full `.ctn` syntax reference, including [values & types](/container-files/values), [services](/container-files/services), and [metadata & tags](/container-files/metadata).
 * **[Using the Container](/usage/container)** — the runtime API for retrieving services and parameters.
 * **[Programmatic API](/advanced/service-binding)** — defining and overriding services from PHP, without a `.ctn` file.
