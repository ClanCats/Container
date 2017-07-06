# Getting Started with the ClanCats Container

Hey there, this is the more detailed version of the Quick Start found in the [README](https://github.com/ClanCats/Container/blob/master/README.md) file. 

**If you are not familiar with dependency injection containers read the [Core Concepts](docs://introduction/core-concepts).**

## Choosing the implementation

Of course how you implement the service container is completly up to you but you should at least decide if you want to compile the dependency graph or not. It is possible to mix a compiled container with dynamic service definitions but for the love of consistent structuring things your really should stick with one way.

You can read more about diffrent types of implementations here:

 * [Basic & dynmaic](docs://usage/implementations/simple)
 * [Compiled](docs://usage/implementations/compiled-container)
 * [Container files](docs://usage/implementations/container-files)

Because the big diffrence between this and any other PHP service container is the meta langauge I will stick with the container files for this getting started guide.

## Setup 

Just like in the README the target directy structure will look like this:

```
app.php
app.container
composer.json
cache/ # make sure this is writable
src/
  Human.php
  SpaceShip.php
  Engine.php
```

### Container Factory



### Engine – Example

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
