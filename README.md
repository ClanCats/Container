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

**Pros:**

 * Minimal overhead and therefore very fast. 
 * Has no additional dependencies.
 * Battle-tested in production serving millions of requests every day.
 * Singleton and Factory service resolvers.
 * Metadata system allowing very intuitive service lookups.
 * A container builder allowing to compile / serialize your service definitions.
 * _Container files_ a simple language to define your services and manage your application config.
 * Composer integration, allowing you to import service definitions from different packages.
 * Lazy service providers for big and dynamic class graphs.

**Things you might not like:**

 * Container allows **only** named services.
 * Currently **no** auto wiring support.
 * Currently **no** IDE Support for _container files_.
 * Having a meta-language might not meet everyone's taste.

## Table of Contents

  * [Performance](#performance)
  * [Installation](#installation)
  * [Documentation 💡](#documentation-)
  * [Quick Start ⚡️](#quick-start-)
    + [Setup](#setup)
    + [Services](#services)
    + [Container file](#container-file)
    + [Container factory](#container-factory)
  * [Usage Examples](#usage-examples)
    + [HTTP Routing using Metadata](#http-routing-using-metadata)
    + [Eventlistener definition](#eventlistener-definition)
    + [Logging handler discovery](#logging-handler-discovery)
  * [Example App](#example-app)
    + [Bootstrap (Container Builder)](#bootstrap--container-builder)
    + [App Container Files](#app-container-files)
  * [ToDo / feature whishlist](#todo-feature-whishlist)
  * [Credits](#credits)
  * [License](#license)

## Performance

This package might seem very heavy for a service container, but after a short warmup, the compiled container is blazing fast and has almost no overhead (3 classes/files). Binding and resolving services dynamically is slower but still won't impact performance in a real-world application.


## Installation

The container follows `PSR-4` autoloading and can be installed using composer:

```
$ composer require clancats/container
```

**Syntax Highlighting**

I've created a basic tmLanguage definition here:
https://github.com/ClanCats/container-tmLanguage

## Documentation 💡

The full documentation can be found on [clancats.io](https://clancats.io/container/master/introduction/getting-started)

## Quick Start ⚡️

Following is just a rough example, a much more detailed and explained guide can be found here: [Getting Started](https://clancats.io/container/master/introduction/getting-started)

### Setup 

Our target directory structure will look like this:

```
app.php
app.ctn
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

Create a new file called `app.ctn` in your applications root folder. 

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
    // create a new container file namespace and parse our `app.ctn` file.
    $namespace = new \ClanCats\Container\ContainerNamespace();
    $namespace->parse(__DIR__ . '/app.ctn');

    // import the namespace data into the builder
    $builder->importNamespace($namespace);
});
```

> Note: Make sure the `../cache` directory is writable.

The variable `$container` contains now a class instance named `AppContainer`.

```php
echo $container->get('firefly')->ayeAye(); // "aye aye captain Reynolds"
```

## Usage Examples

### HTTP Routing using Metadata

Your can use the container metadata to define routes directly with your service definitions:

```
@controller.dashboard.home: App\Controller\Dashboard\HomepageAction
    = route: {'GET'}, '/dashboard/home'

@controller.dashboard.sign_in: App\Controller\Dashboard\SignInAction
    = route: {'GET', 'POST'}, '/dashboard/signin'

@controller.dashboard.sign_out: App\Controller\Dashboard\SignOutAction
    = route: {'GET'}, '/logout'

@controller.dashboard.client: App\Controller\Dashboard\ClientDetailAction
    = route: {'GET'}, '/dashboard/clients/me'
    = route: {'GET'}, '/dashboard/clients/{clientId}'
```

Now obviously this is depending on your routing implementation. You are able to fetch all services with a routing definition like so:

Example using FastRoute:

```php
$dispatcher = \FastRoute\cachedDispatcher(function(RouteCollector $r) use($container)
{
    foreach($container->serviceNamesWithMetaData('route') as $serviceName => $routeMetaData)
    {
        // an action can have multiple routes handle all of them
        foreach($routeMetaData as $routeData)
        {
            $r->addRoute($routeData[0], $routeData[1], $serviceName);
        }
    }
}, [
    'cacheFile' => PATH_CACHE . '/RouterCache.php',
    'cacheDisabled' => $container->getParameter('env') === 'dev',
]);
```

### Eventlistener definition

Just like with the routing you can use the meta data system to define eventlisteners:

```
@signal.exception.http404: App\ExceptionHandler\NotFoundExceptionHandler
  = on: 'http.exception', call: 'onHTTPException'

@signal.exception.http400: App\ExceptionHandler\BadRequestExceptionHandler
  = on: 'http.exception', call: 'onHTTPException'

@signal.exception.http401: App\ExceptionHandler\UnauthorizedAccessExceptionHandler
  = on: 'http.exception', call: 'onHTTPException'

@signal.bootstrap_handler: App\Bootstrap
    = on: 'bootstrap.pre', call: 'onBootstrapPre'
    = on: 'bootstrap.post', call: 'onBootstrapPost'
```

And then in your event dispatcher register all services that have the matching metadata.

The following example shows how the implementation could look like. Copy pasting this will not just work.

```php
foreach($container->serviceNamesWithMetaData('on') as $serviceName => $signalHandlerMetaData)
{
    // a action can have multiple routes handle all of them
    foreach($signalHandlerMetaData as $singalHandler)
    {
        if (!is_string($singalHandler[0] ?? false)) {
            throw new RegisterHandlerException('The signal handler event key must be a string.');
        }

        if (!isset($singalHandler['call']) || !is_string($singalHandler['call'])) {
            throw new RegisterHandlerException('You must define the name of the function you would like to call.');
        }

        $priority = $singalHandler['priority'] ?? 0;

        // register the signal handler
        $eventdispatcher->register($singalHandler[0], function(Signal $signal) use($container, $singalHandler, $serviceName)
        {
            $container->get($serviceName)->{$singalHandler['call']}($signal);
        }, $priority);
    }
}
```

### Logging handler discovery

Or maybe you have a custom framework that comes with a monolog logger and you want to make it easy to add custom log handlers per integration:

```
/**
 * Log to Graylog
 */
:gelf.host: 'monitoring.example.com'
:gelf.port: 12201

@gelf.transport: Gelf\Transport\UdpTransport(:gelf.host, :gelf.port)
@gelf.publisher: Gelf\Publisher(@gelf.transport)
@logger.error.gelf_handler: Monolog\Handler\GelfHandler(@gelf.publisher)
  = log_handler

/**
 * Also send a slack notification 
 */
@logger.,error.slack_handler: Example\MyCustom\SlackWebhookHandler('https://hooks.slack.com/services/...', '#logs')
    = log_handler
```

And your framework can simply look for services exposing a `log_handler` meta key:

```php
// gather the log handlers
$logHandlerServices = array_keys($container->serviceNamesWithMetaData('log_handler'));

// bind the log hanlers
foreach($logHandlerServices as $serviceName) {
    $logger->pushHandler($container->get($serviceName));
}
```

## Example App

This should showcase a possible structure of an application build using the CCContiner. This is a simplified version of what we use in our private service framework.

Folder structure:

```
# The main entry point for our container application
app.ctn

# A per environment defined config. This file
# is being generated by our deployment process 
# individually for each node.
app.ctn.env 

# We like to but all other container files in one directory
app/
  # Most configuration parameters go here
  config.ctn

  # Command line commands are defined here
  commands.ctn

  # Application routes (HTTP), actions and controllers 
  routes.ctn

  # General application services. Depending on the size of 
  # the project we split the services into more files to keep
  # things organized. 
  services.ctn

# PHP Bootstrap
bootstrap.php

# Composer file
composer.json

# A writable directory for storing deployment 
# depndent files.
var/
  cache/

# PHP Source 
src/
  Controller/
    ListBlogPostController.php
    GetBlogPostController.php

  Commands/
    CreateUserCommand.php

  Servies/
    UserService.php
    BlogService.php
```

### Bootstrap (Container Builder)

This container builder does a few things:

 * Imports container namespaces from packages installed using composer.
 * Scans the `./app` directory for ctn files.
 * Adds the env container file to the namespace.

```php
<?php 
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }

define('PATH_ROOT',         __DIR__);
define('PATH_CACHE',        PATH_ROOT . DS . 'var' . DS . 'cache');
define('PATH_APPCONFIG',    PATH_ROOT . DS . 'app');

$factory = new \ClanCats\Container\ContainerFactory(PATH_CACHE);

$container = $factory->create('AppContainer', function($builder)
{
    $importPaths = [
        'app.env' => PATH_ROOT . '/app.ctn.env',
    ];

    // find available container files
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PATH_APPCONFIG));

    foreach ($rii as $file) 
    {
        // skip directories
        if ($file->isDir()) continue;

        // skip non ctn files
        if (substr($file->getPathname(), -4) !== '.ctn') continue;

        // get the import name
        $importName = 'app' . substr($file->getPathname(), strlen(PATH_APPCONFIG), -4);

        // add the file
        $importPaths[$importName] = $file->getPathname();
    }

    // create a new container file namespace and parse our `app.ctn` file.
    $namespace = new \ClanCats\Container\ContainerNamespace($importPaths);
    $namespace->importFromVendor(PATH_ROOT . '/vendor');

    // start with the app file
    $namespace->parse(__DIR__ . '/app.ctn');

    // import the namespace data into the builder
    $builder->importNamespace($namespace);
});
```

### App Container Files

The first file `app.ctn` has mainly one job. That is simply to include other files and therefore define the order they are being read.

`app.ctn`:

```
/**
 * Import the configuration
 */
import app/config

/**
 * Import the services
 */
import app/services

/**
 * Import the actions & routes
 */
import app/routes

/**
 * Import the commands
 */
import app/commands

/**
 * Load the environment config last so it is
 * able to override most configs.
 */
import app.env
```

## ToDo / feature whishlist

- Container Files
  - [x] Metadata support
  - [x] Array Support
  - [x] Alias Support
  - [ ] Container file Namespace support
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

## Credits

- [Mario Döring](https://github.com/mario-deluna)
- [All Contributors](https://github.com/ClanCats/Container/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/ClanCats/Container/blob/master/LICENSE) for more information.
