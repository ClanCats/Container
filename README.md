# ClanCats Container

A PHP Service Container with fast and compilable dependency injection. 

**Features:**

 * **Singleton** and **prototype** service resolvers.
 * A **container builder** allowing to **compile** your service definitions.
 * Container files featuring a meta language to define your services.
 * Composer integration, allowing you to import default service definitions from your dependencies.
 * Lazy **service providers** for big and dynamic class graphs.

**Cons:**

 * Container allows **only** named services.
 * Currently **no** auto wiring.
 * Obviously no IDE Support for _container files_.

[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)](https://github.com/ClanCats/Container/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.1_

## Why should I use this? 

Don't, at least not at this stage. The container is not battle tested and is only in use on some small production systems. At this point, I still might change the public API or brake functionality. Feel free to try this out on small side projects. Obviously, I really appreciate everyone who wants to sacrifice their time to contribute.


## Installation

The container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

## Documentation

The full documentation can be found on [http://clancats.io/container](http://clancats.io/container/master/)

## Basic Usage

### Container file

Create an instance of the container `ContainerFactory` to build a container from a given container file.

```php
use ClanCats\Container\ContainerFactory;

$factory = new ContainerFactory(__DIR__ . '/cache');

$container = $factory->createFromFile('App', '~/application.container');
```

### Dynamic service container

Create an instance of the base container.

```php
use ClanCats\Container\Container;

$contanier = new Container();
```

This is the simplest and the most dynamic implementation. This type of container cannot be compiled. Which makes it a little bit slower, but it therefor has almost no limitations when it comes to service binding and your parameters.



Note: Take a look at the `ContainerFactory` to make use of the compilable `ContainerBuilder`. Compiling your container reduces the overhead to a minimum and creates a big performance boost. 

Next our example services / classes will look like the following:

```php
class Human
{
	public $name;

	public function setName(string $name) {
		$this->name = $name;
	}
}

class SpaceShip 
{
	protected $captain; // every ship needs a captain!

	public function __construct(Human $captain) {
		$this->captain = $captain;
	}

	public function ayeAye()
	{
		return 'aye aye captain ' . $this->captain->name;
	}
}
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

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.
