<p align="center"><a href="http://clancats.io/container/master/" target="_blank">
    <img width="100px" src="http://clancats.io/assets/media/img/logo/container.png">
</a></p>

# ClanCats Container

A PHP Service Container featuring a simple meta language with fast and compilable dependency injection. 

[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)](https://github.com/ClanCats/Container/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.0_

**Features:**

 * **Singleton** and **prototype** service resolvers.
 * A container builder allowing to **compile** your service definitions.
 * _Container files_ featuring a **meta language** to define your services.
 * **Composer** integration, allowing you to import default service definitions from your dependencies.
 * **Lazy service providers** for big and dynamic class graphs.

**Cons:**

 * Container allows **only** named services.
 * Currently **no** auto wiring.
 * Obviously **no** IDE Support for _container files_.

## Why should I use this? 

Don't, at least not at this stage. The container is not battle tested and is only in use on some small production systems. At this point, I still might change the public API or brake functionality. Feel free to try this out on small side projects. Obviously, I really appreciate everyone who wants to sacrifice their time to contribute.

## Performance

This package might seem very heavy for a service container, but after a short warmup the compiled container is blazing fast and has almost no overhead (3 Classes). Binding and resolving services dynamically is slower but still won't impact performance in real world application.

## Installation

The container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

## Documentation

The full documentation can be found on [http://clancats.io/container](http://clancats.io/container/master/)

## Quick Start

Following is just a really rough example, a much more detailed and explained guide can be found here: [Getting Started](http://clancats.io/container/master/introduction/getting-started)

### Setup 

Our target directy structure will look like this:

```
app.php
app.container
composer.json
src/
  Human.php
  SpaceShip.php
```

### Services

To demenstrate how to use this service container we need to create two classes a `SpaceShip` and a `Human`.

Create a new php file `src/Human.php`:

```php
class Human
{
    public $name;

    public function setName(string $name) {
        $this->name = $name;
    }
}
```

Create another php file `src/SpaceShip.php`:

```php
class SpaceShip
{
    protected $captain; // every ship needs a captain!

    public function __construct(Human $captain) {
        $this->captain = $captain;
    }

    public function ayeAye() {
        return 'aye aye captain ' . $this->captain->name;
    }
}
```

### Container file

A container file allows you to bind your services & parameters using a simple meta language. 

> Note: This feature is entirely optional if you prefer binding your services in PHP itself read: [](http://clancats.io/container/master/service-binding/basics)

Create a new file called `app.container` in your applications root folder. 

```
@malcolm: Human
    - setName: 'Reynolds'

@firefly: SpaceShip(@malcolm)
```

### Container factory

Now we need to parse the container file and compile it as a new class. For this task we create the `app.php` file.

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder)
{
    // create a new container namespace
    $namespace = new \ClanCats\Container\ContainerNamespace();

    // forward the parsed data to the container builder
    $builder->addArray($namespace->parse(__DIR__ . '/app.container'));
});
```

The variable `$container` contains now a class instance named `AppContainer`.

```php
echo $container->get('firefly')->ayeAye() // "aye aye captain Reynolds"
```

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.
