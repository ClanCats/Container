# Getting Started with the ClanCats Container

Hey there, this is the more detailed version of the Quick Start found in the [README](https://github.com/ClanCats/Container/blob/master/README.md) file. 

**If you are not familiar with dependency injection containers read the [Core Concepts](docs://introduction/core-concepts).**

## Choosing the implementation

Of course how you implement the service container is completly up to you but you should at least decide if you want to compile the dependency graph or not. It is possible to mix a compiled container with dynamic service definitions but for the love of consistent structuring things your really should stick with one way.

You can read more about diffrent types of implementations here:

 * [Default](docs://usage/implementations/default)
 * [Dynamic](docs://usage/implementations/dynamic)
 * [Custom build](docs://usage/implementations/custom-build)

Because the main diffrence between this and any other PHP service container is the meta langauge I will stick with it for this getting started guide.

## Setup 

Just like in the README the target directy structure will look like this:

```
app.php
app.ctn # this will be our container file.
app_config.ctn
composer.json
cache/ # make sure this is writable
src/
  Human.php
  SpaceShip.php
  Engine.php
```

## Container Factory

To construct a container instance we make use of the `ContainerFactory` which will generate a PHP file containig our very own container.

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder)
{
    // create a new container file namespace and parse our `app.ctn` file.
    $namespace = new \ClanCats\Container\ContainerNamespace([
        'config' => __DIR__ . '/app_config.ctn',
    ]);
    $namespace->parse(__DIR__ . '/app.ctn');

    // import the namespace data into the builder
    $builder->importNamespace($namespace);
});
```

And thats it, we are now able to modify the `app.ctn` file which will build our Container instance.

### Explain

Not informative egnouth? I'm sorry let me get a bit more into detail what happens here:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');
```

The container factory has not alot of functionality its purpuse is to write and read the genrerated PHP class. It supports a _debug mode_ by simply setting the second argument to `true`. In the _debug mode_ the factory will ignore if a builded file is already present and rebuild it every time.

```php
$container = $factory->create('AppContainer', function($builder)
```

The `create` method as you probably already guessed is where the container instance is beeing created. The first argument is the class name. Namespaces are supported so you could also set it to something like `Acme\MainBundle\MainContainer`. As a second parameter we have a callback where we define what the container should actually contain.

Read more about this here: [Container Factory](docs://@todo/)

```php 
$namespace = new \ClanCats\Container\ContainerNamespace([
    'config' => __DIR__ . '/app_config.ctn',
]);
```

Okey so what the hell is a container namespace? 
Well look at it as a little application with its own file structure. The container namespace defines this file structure. `config` is not a special key, its just a name we assign to a file that shold be accessible in your container files / scripts.

```php
$namespace->parse(__DIR__ . '/app.ctn');
```

Now we have to parse the main file. All parsed data is now assigned to our namespace instance.

Read more about this here: [Container Namepsace](docs://@todo/)

```php 
$builder->importNamespace($namespace);
```

Finally we feed our namespace into the builder object.

> Note: Before we continue here, check out [Container File Syntax](docs://@todo/). There is also a `tmLanguage` available for syntax highlighting support of `ctn` files.

## Parameters 

Parameters are always prefixed with a `:` character and can be defined in any order. They can hold scalar values (array support is also planned.) when defined inside a `ctn` file. Technically there is no limitation on what a parameter can contain, you can set a parameter containing anything you want manually `$container->setContainer('mykey', <a value>)`.

You might have noticed that in the setup there are two `ctn` files mentiond. Lets go on and create those: `app.ctn` and `app_config.ctn`.

Now in the `app.ctn` define a parameter like this:

```ctn
:firstname: = 'James'
```

You can access the parameter of the container anytime:

```php
echo $container->getParameter('firstname');
```

And then define the lastname inside the `app_config.ctn`:

```ctn
:lastname: = 'Bond'
```

If we know would try to access lastname, we would get `null`. Thats because we need to import our `app_config.ctn` into our main `app.ctn`. doing so is simple:

```ctn
import config
:firstname: = 'James'
```

Remember where we constructed the container namespace? We defined the name of the `app_config.ctn` to be simply `config`.

This particular example might seem a bit useless, well ok, it is usless. But I like to seperate configuration from the service definitions and this is a neat way to do so.
 
## Engine – Example

The first class we create is the engine later needed for our spaceships.

The `src/Engine.php` class is constructed with a given power and an amount of fuel. It can be throttlet up for a given amount of time which will return the traveled distance and consume fuel. With the `refuel` method the engine can be, well you probably already guessd it. Also a mechanic can be assigned to the engine.

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

    public function throttle(int $for) {
        $this->fuel -= ($distance = $this->power * $for); return $distance;
    }

    public function refuel(int $amount) {
        if ($this->mechanic) $this->fuel += $amount;
    }

    public function setMechanic(Human $mechanic) {
        $this->mechanic = $mechanic;
    }
}
```

So lets power the engine up a bit! 

## Choosing the implementation

Of course how you implement the service container is completly up to you but you should at least decide if you want to compile the dependency graph or not. It is possible to mix a compiled container with dynamic service definitions but for the love of structuring things your really should stick with one way.


The key diffrence between 

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
And finally the company object constructs with a _string_ which represents the companies name.

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
