<p align="center"><a href="http://clancats.io/container/master/" target="_blank">
    <img width="100px" src="http://clancats.io/assets/media/img/logo/container.png">
</a></p>

# ClanCats Container

A PHP Service Container featuring a simple meta-language with fast and compilable dependency injection. 

[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)](https://github.com/ClanCats/Container/blob/master/LICENSE)
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.0_

**Features:**

 * **Singleton** and **prototype** service resolvers.
 * A container builder allowing to **compile/serialize** your service definitions.
 * _Container files_ featuring a **meta language** to define your services.
 * **Composer** integration, allowing you to import default service definitions from your dependencies.
 * **Lazy service providers** for big and dynamic class graphs.

**Cons:**

 * Container allows **only** named services.
 * Currently **no** auto wiring support.
 * Obviously **no** IDE Support for _container files_.
 * Having a meta-language might not meet everyone's taste.
 * Does not depend on the PSR-11 dependency, you also might take this as a Pro.

## Why should I use this? 

Don't, at least not at this stage. The container is not battle tested and is only in use on some small production systems. At this point, I still might change the public API or break functionality. Feel free to try this out on small side projects. Obviously, I appreciate everyone who wants to sacrifice their time to contribute.

## Performance

This package might seem very heavy for a service container, but after a short warmup the compiled container is blazing fast and has almost no overhead (3 classes/files). Binding and resolving services dynamically is slower but still won't impact performance in real-world application.

## Installation

The container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

## Documentation 💡

The full documentation can be found on [clancats.io](https://clancats.io/container/master/introduction/getting-started)

## Quick Start ⚡️

Following is just a rough example, a much more detailed and explained guide can be found here: [Getting Started](https://clancats.io/container/master/introduction/getting-started)

### Setup 

Our target directory structure will look like this:

```
app.php
app.container
composer.json
cache/ # make sure this is writable
src/
  Human.php
  SpaceShip.php
```

### Services

To demonstrate how to use this service container we need to create two classes a `SpaceShip` and a `Human`.

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

A container file allows you to bind your services & parameters using a simple meta-language. 

> Note: This feature is entirely optional if you prefer binding your services in PHP itself read: [Service Binding](https://clancats.io/container/master/advanced/service-binding)

Create a new file called `app.container` in your applications root folder. 

```
@malcolm: Human
    - setName('Reynolds')

@firefly: SpaceShip(@malcolm)
```

### Container factory

Now we need to parse the container file and compile it as a new class. For this task, we create the `app.php` file. There you need to **require the composer autoloader** and require your source files or configure composer to autoload the classes from the `src/` directory.

```php
require "vendor/autoload.php";

// for the consistency of the example I leave this here 
// but I strongly recommend to autolaod your classes with composer.
require "src/SpaceShip.php";
require "src/Human.php";

$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder)
{
    // create a new container file namespace and parse our `app.container` file.
    $namespace = new \ClanCats\Container\ContainerNamespace();
    $namespace->parse(__DIR__ . '/app.container');

    // import the namespace data into the builder
    $builder->importNamespace($namespace);
});
```

> Note: Make sure the `../cache` directory is writable.

The variable `$container` contains now a class instance named `AppContainer`.

```php
echo $container->get('firefly')->ayeAye(); // "aye aye captain Reynolds"
```

## ToDo / feature whishlist

- Container Files
  - [ ] Metadata support
  - [ ] Container file Namespace support
  - [ ] Use PHP namespace support
  - [ ] Prototype service support
  - [ ] Factory Support
  - [x] Array Support
  - [ ] Override stack (meta and calls)
  - [ ] Autowiring by "using trait"
  - [ ] Autowiring by "instance of"
  - [ ] Autowiring by "has method"
  - [ ] Property injection
  - [ ] Parameter concatination
  - [ ] Input Parameters (used for env detection)
  - [ ] Late service override (allow for adding meta or arguments) 
  - [ ] macros
- Container
  - [x] Metadata support
  - [ ] Property injection
  - [ ] Call stacks

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.
