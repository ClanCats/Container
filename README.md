# ClanCats Container

A PHP Service Container with fast and compilable dependency injection. 

**Key features:**

 * **Singleton** and **prototype** service resolvers.
 * A **container builder** allowing to **compile** your service definitions.
 * Lazy **service providers** for big and dynamic class graphs.


[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)]()
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.1_

## Installation

The Container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

## Usage

### Example

For the most of the following examples please consider the following setup.

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


## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.