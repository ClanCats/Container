# ClanCats Container

A PHP Service Container with fast and compilable dependency injection. 

**Features:**

 * **Singleton** and **prototype** service resolvers.
 * A **container builder** allowing to **compile** your service definitions.
 * Lazy **service providers** for big and dynamic class graphs.

**Cons:**

 * Container only allows **only** named services.
 * Currently **no** autowiring.

[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)](https://github.com/ClanCats/Container/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.1_

## Installation

The Container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

## Usage

### Example

> Note: Check out the [Example Setup](http://clancats.io/master/usage/examples#setup-1).

Now its time to create a  container instance:

```php
use ClanCats\Container\Container;

$contanier = new Container();
```

Note: Use the `ContainerFactory` to create your container instance to make use of the compilable `ContainerBuilder`.

```php
// bind "Massive Industries" as producer company.
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

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.
