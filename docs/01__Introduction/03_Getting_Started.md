# Getting Started with the ClanCats Container

Hey there, this is the more detailed version of the Quick Start found in the [README](https://github.com/ClanCats/Container/blob/master/README.md) file. 

**If you are not familiar with dependency injection containers read the [Core Concepts](docs://introduction/core-concepts).**

## Choosing the implementation

Of course how you implement the service container is completely up to you but you should at least decide if you want to compile the dependency graph or not. It is possible to mix a compiled container with dynamic service definitions but for the love of consistent structuring things, your really should stick with one way.

You can read more about different types of implementations here:

 * [Default](docs://usage/implementations/default)
 * [Dynamic](docs://usage/implementations/dynamic)
 * [Custom build](docs://usage/implementations/custom-build)

Because the main difference between this and any other PHP service container is the meta language I will stick with it for this getting started guide.

## Setup 

Just like in the README the target directory structure will look like this:

```
app.php
app.ctn # this will be our container file.
config.ctn # this will be our configuration file.
composer.json
cache/ # make sure this is writable
src/
  Human.php
  SpaceShip.php
  Engine.php
```

## Container Factory

To construct a container instance we make use of the `ContainerFactory` which will generate a PHP file containing our very own container.

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder)
{
    // create a new container file namespace and parse our `app.ctn` file.
    $namespace = new \ClanCats\Container\ContainerNamespace([
        'config' => __DIR__ . '/config.ctn',
    ]);
    $namespace->parse(__DIR__ . '/app.ctn');

    // import the namespace data into the builder
    $builder->importNamespace($namespace);
});
```

And that's it, we are now able to modify the `app.ctn` file which will build our Container instance.

### Explain

Not informative enough? I'm sorry let me get a bit more into detail what happens here:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');
```

The container factory has not a lot of functionality its purpose is to write and read the generated PHP class. It supports a _debug mode_ by simply setting the second argument to `true`. In the _debug mode_ the factory will ignore if a built file is already present and rebuild it every time.

```php
$container = $factory->create('AppContainer', function($builder)
```

The `create` method as you probably already guessed is where the container instance is being created. The first argument is the class name. Namespaces are supported so you could also set it to something like `Acme\MainBundle\MainContainer`. As a second parameter, we have a callback where we define what the container should actually contain.

Read more about this here: [Container Factory](docs://@todo/) & [Container Builder](docs://@todo/)

```php 
$namespace = new \ClanCats\Container\ContainerNamespace([
    'config' => __DIR__ . '/config.ctn',
]);
```

Okay so what the hell is a container namespace? 
Well, look at it as a little application with its own file structure. The container namespace defines this file structure. `config` is not a special key, it's just a name we assign to a file that should be accessible in your container files/scripts.

Now we have to parse the main file.

```php
$namespace->parse(__DIR__ . '/app.ctn');
```

All parsed data (servies, parameters) is now assigned to our namespace instance.

Read more about this here: [Container Namepsace](docs://@todo/)

```php 
$builder->importNamespace($namespace);
```

Finally we feed our namespace into the builder object.

> Note: Before we continue here, you might want to check out **[Container File Syntax](docs://container-files/syntax)**. There is also a `tmLanguage` available for syntax highlighting support of `ctn` files.

## Parameters

Parameters are always prefixed with a `:` character and can be defined in any order. When defined inside of a `ctn` file, they can hold scalar values and arrays. Technically the container has no limitation on what a parameter can contain, you can set a parameter containing anything you want manually with the `setParamter` method.

Now in the `app.ctn` define some parameter like this:

```ctn
:firstname: = 'James'
:lastname: = 'Bond'
:code: 007
```

You can access the parameters of the container anytime:

```php
echo $container->getParameter('firstname');
```

## Importing 

You might have noticed that in the setup there are two `ctn` files mentioned. Let's go on and create the `config.ctn`.

Inside the `config.ctn` we define another parameter:

```ctn
:missions.available: {
    'Goldeneye',
    'Goldfinger',    
}
```

If we know would try to access `missions.available`, we would get `null`. That's because we need to import our `config.ctn` into our main `app.ctn`. doing so is simple:

```ctn
import config

:firstname: = 'James'
:lastname: = 'Bond'
:code: 007
```

Remember where we constructed the container namespace? We defined the name of the `config.ctn` to be simply `config`.

This particular example (with firstname, lastname) might seem a bit useless.
I like to separate configuration from the service definitions, using imports are a neat way to do so.

## Service definitions


### Example Setup – Engine

The first class we are going to create is the engine. This is class has nothing to do with the container itself, it purely acts as a demonstration.

The `src/Engine.php` class is constructed with a given power and an amount of fuel. It can be throttled up for a given amount of time which will return the traveled distance and consume fuel. With the `refuel` method the engine can be, well you probably already guessed it. Also, a mechanic can be assigned to the engine.

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

So let's define our engine as a service.

```ctn 
@hyperdrive: Engine(500, 10000)
```

Now we are able to load the hyperdrive engine using the container and test it out.

```php
$hyperdrive = $container->get('hyperdrive');

echo 'current fuel: ' . $hyperdrive->getFuel() . PHP_EOL; // 10000
echo 'traveling : ' . $hyperdrive->throttle(5) . PHP_EOL; // 2500
echo 'current fuel: ' . $hyperdrive->getFuel() . PHP_EOL; // 7500
```

Often we don't want to hardcode the constructor arguments, that's where parameters come in handy. 

```ctn 
:hyperdrive.power: 500
:hyperdrive.fuel: 10000

@hyperdrive: Engine(:hyperdrive.power, :hyperdrive.fuel)
```

But we still can not refuel our engine without a mechanic. This brings us to the next example.

### Example Setup – Human

The second example class will be a Human with only one argument in the constructor which represents the name. 

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

I'm a big Firefly fan so excuse all the references. Here comes our mechanic. 

```ctn
@kaylee: Human('Kaylee Frye')
  - setJob('Mechanic')
```

Now we are also able to assign Kaylee as a mechanic to our engine.

```ctn
@hyperdrive: Engine(:hyperdrive.power, :hyperdrive.fuel)
  - setMechanic(@kaylee)
```

And voila we are able to refuel:

```php
$hyperdrive = $container->get('hyperdrive');

$hyperdrive->getFuel();
$hyperdrive->throttle(5);
$hyperdrive->refuel(1000);
echo 'current fuel: ' . $hyperdrive->getFuel() . PHP_EOL; // 8500
```

## Choosing the implementation

Of course how you implement the service container is completely up to you but you should at least decide if you want to compile the dependency graph or not. It is possible to mix a compiled container with dynamic service definitions but for the love of structuring things you really should stick with one way.


The key difference between 

please consider the following setup.

We have 3 _Classes_: `SpaceShip`, `Engine` and `Company`:

```php
class SpaceShip 
{
    public $engine;
    public $producer;

    public function __construct(Engine $engine, Company $producer)
    {
        $this->engine = $engine;
        $this->producer = $producer;
    }
}
```

Our beautiful `SpaceShip` knows two dependencies, _1._ Its engine and _2._ The company who built it.

```php
class Engine 
{
    public $power = 10;

    public function setPower(int $power) : void
    {
        $this->power = $power;
    }
}
```

The engine object has no constructor arguments, but is able to mutate its power using the `setPower` method.

```php
class Company 
{
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
```
And finally the company object constructs with a _string_ which represents the company's name.

## Usage

Create a new instance of the base container:

```php
use ClanCats\Container\Container;

$contanier = new Container();
```

Note: Use the `ContainerFactory` to make use of the compilable `ContainerBuilder`.

```php
$contanier->bind('producer', Company::class)
    ->arguments(['Massive Industries']);
```

Binds the company service under the name `producer` and add the constructor argument "Massive Industries".

```php
echo $container->get('producer')->name; // "Massive Industries"
```

Bind the rest.

```php
// bind the pulsedrive engine and set the power
// the boolean flag at the end indicated that this is 
// NOT a shared service.
$contanier->bind('pulsedrive', Engine::class, false)
    ->calls('setPower', [20]);

// bind a "shuttle" spaceship, inject the pulsedrive and 
// set the producer company 
$contanier->bind('shuttle', SpaceShip::class, false)
    ->arguments(['@pulsedrive', '@producer']);
```

When we are all set we can start creating shuttles:

```php
$jumper1 = $container->get('shuttle');
$jumper2 = $container->get('shuttle');

// note: the producer is binded as
$jumper1->producer === $jumper1->producer; // true
```

Bind the captain to the service container.

```php
$contanier->bind('malcolm', \Human::class)
    ->calls('setName', ['Reynolds']);

$container->get('malcolm'); // returns \Human instance
```
And what is a captain without his ship?..

```php
$contanier->bind('firefly', \SpaceShip::class)
    ->arguments(['@malcolm']);
```

The `@` character tells the container to resolve the dependency named *malcolm*.

```php
echo $container->get('firefly')->ayeAye(); // aye aye captain Reynolds
```