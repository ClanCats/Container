# Examples

## Setup

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

The engine object has no constructor arguments but is able to mutate its power using the `setPower` method.

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

// bind a "shuttle" space ship, inject the pulsedrive and 
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
