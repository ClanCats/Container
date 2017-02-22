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