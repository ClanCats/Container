# ClanCats Container

A PHP Service Container with fast and compilable dependency injection. 

**Features:**

 * **Singleton** and **prototype** service resolvers.
 * A **container builder** allowing to **compile** your service definitions.
 * Container files featuring a meta language to define your services.
 * Composer integration, allowing to import default service definitions from your dependencies.
 * Lazy **service providers** for big and dynamic class graphs.

**Cons:**

 * Container allows **only** named services.
 * Currently **no** autowiring.
 * Obviously no IDE Support for _container files_.

[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)](https://github.com/ClanCats/Container/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.1_

Why should I use this? Don't at least not at this stage. The container is not battle tested and is only in use on some small production systems. At this point I still might change the public API or brake functionality. Feel free to try this out on small side projects. Obviously I really appriciate everyone who wants to sacrifice time to contribute.

## Installation

The Container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

## Documentation

The full documentation can be found on: [http://clancats.io/container](http://clancats.io/container/master/)

## Getting Started

Here follow some really basic examples to get started with the clancats container.

### Dynamic Service Container

Create a new instance of the base container. This implementation type is fully dynamic and allows 

```php
use ClanCats\Container\Container;

$contanier = new Container();
```

Note: Use the `ContainerFactory` to make use of the compilable `ContainerBuilder`.

Bind a service to the container instance:

```php
$contanier->bind('router', \Acme\Router::class);

$container->get('router'); // returns \Acme\Router instance
```

Bind a service with constructor arguments

```php
$contanier->bind('router', \Acme\Router::class);
```

```php
$contanier->bind('logger', \Monolog\Logger::class)
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

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.
