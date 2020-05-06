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
 * In production serving millions of requests every day.
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
    + [App Config with Environment](#app-config-with-environment)
    + [Aliases / Services Definitions / Parameters Example](#aliases---services-definitions---parameters-example)
    + [HTTP Routing using Metadata](#http-routing-using-metadata)
    + [Eventlisteners using Metadata](#eventlisteners-using-metadata)
    + [Logging handler discovery](#logging-handler-discovery)
  * [Example App](#example-app)
    + [Bootstrap (Container Builder)](#bootstrap--container-builder)
    + [App Container Files](#app-container-files)
  * [Container File Syntax](#container-file-syntax)
    + [Types](#types)
      - [Numbers](#numbers)
      - [Strings](#strings)
      - [Booleans and Null](#booleans-and-null)
      - [Arrays](#arrays)
    + [Parameters](#parameters)
    + [Service Definition](#service-definition)
      - [Constructor](#constructor)
        * [Referenced arguments](#referenced-arguments)
      - [Method calls](#method-calls)
      - [Service metadata](#service-metadata)
    + [Imports](#imports)
    + [Overriding](#overriding)
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

### App Config with Environment

Container parameters are nothing more than values that are globally available in your container.
We use them to store most static config values and also to handle different environments.

For this, we usually create two files. In this example:
  
  * `config.ctn` The main configuration file.
  * `config.ctn.env` Environment specific overrides.


`config.ctn`:

```
// default environment
:env: 'stage'

// debug mode
:debug: false

// Firewall whitelist
:firewall.whitelisted_ips: {
    '127.0.0.1': 'Local',
    '1.2.3.4': 'Some Office',
    '4.3.2.1': 'Another Office',
}

// application name
:app.name: 'My Awesome application'

import config.env
```

`config.ctn.env`:

```
override :env: 'dev'
override :debug: true

// Firewall whitelist
override :firewall.whitelisted_ips: {
    '127.0.0.1': 'Local',
    '192.168.33.1': 'MyComputer',
}
```

In PHP these values are then accessible as parameters. For this, to work you need to configure the correct import paths in your container namespace. You find an example of that in the [Example App](#example-app).

```php
echo $container->getParameter('app.name'); // 'My Awesome application'
echo $container->getParameter('env'); // 'dev'
echo $container->getParameter('debug'); // true
```

### Aliases / Services Definitions / Parameters Example

```
# Parameters can be defined erverywhere
:pipeline.prefix: 'myapp.'

// you can define aliases to services
@pipeline.queue: @queue.redis
@pipeline.storage: @db.repo.pipeline.mysql

// add function calls that will be run directly after construction of the service
@pipeline: Pipeline\PipelineManager(@pipeline.queue, @pipeline.storage, @pipeline.executor)
  - setPrefix(:pipeline.prefix)
  - bind(@pipeline_handler.image.downloader)
  - bind(@pipeline_handler.image.process)

@pipeline_handler.image.downloader: PipelineHandler\Images\DownloadHandler(@client.curl)
@pipeline_handler.image.process: PipelineHandler\Images\ProcessHandler(@image.processor, { 
  'temp_dir': '/tmp/', 
  'backend': 'imagick'
})
```

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

### Eventlisteners using Metadata

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

## Container File Syntax

Container files are written in a very simple meta language.

### Types

The language supports the following scalar types:

 * **Strings** Single and double quoted. <br>
   `'hello'` & `"world"`
 * **Numbers** float / double, int. <br>
    `3.14`, `42`
 * **Booleans** <br>
     `true` and `false`.
 * **Null** <br>
    `null`
 * **Arrays** list and associative. <br>
   `{'A', 'B', 'C'}`, `{'A': 10, 'B': 20}`

#### Numbers

Container files do not differentiate between different number types because it would be an unnecessary overhead, we forward that job directly to PHP.

```
42 ## Int
42.01 ## Float
-42.12345678912345 ## Double
```

That means that also the floating point precision is handled by PHP. All values are interpreted means large doubles might be stored rounded.

#### Strings

Strings must always be encapsulated with a single `'` or double `"` quote. This serves mainly a comfort purpose when having many quotes inside your string not having to escape them all.

Escaping of special characters works just the usual way. 

```
:say: 'Hello it\'s me!'`
```

Beloved or Hated emojis will also work just fine. 

```
:snails: '🐌🐌🐌'
```

#### Booleans and Null

There is not much to say about them:

```
:nothing: null
```

```
:positive: true
:negative: false
```

#### Arrays

It's important to notice that all arrays are internally associative. When defining a simple list the associative key is automatically generated and represents the index of the item.

This means that the array `{'A', 'B'}` equals `{0: 'A', 1: 'B'}`.

Arrays can be defined multidimensional:

```yml
{
    'title': 'Some catchy title with Star Wars',
    'tags': {'top10', 'movies', 'space'},
    'body': 'Lorem ipsum ...',
    'comments': 
    {
        {
            'text': 'Awesome!',
            'by': 'Some Dude',
        }
    }
}
```

### Parameters

Parameters or configuration values can also be defined inside the container files. 

A parameter is always prefixed with a `:` character.

```yml
:database.hostname: "production.db.example.com"
:database.port: 7878
:database.cache: true
```

### Service Definition

A service definition is always named and must be prefixed with a `@` character. 

```yml
## <service name>: <class name>
@log.adapter: FileAdapter
```

The class name can contain the full namespace.

```yml
@log.adapter: Acme\Log\FileAdapter
```
#### Constructor

Constructor arguments can be passed after the class name. 

```yml
@dude: Person("Jeffery Lebowski")
```

##### Referenced arguments

Arguments can reference a parameter or service.

```yml
:name: 'Jeffery Lebowski'

@dude: Person(:name)
```

```yml
@mysql: MySQLAdapter('localhost', 'root', '')

@repository.posts: Repositories/Post(@mysql)
```

#### Method calls

Method calls can be assigned to a service definition.

```yml
@jones: Person('Duncan Jones')
@sam: Person('Sam Rockwell')

@movie.moon: Movie('Moon')
  - setDirector(@jones)
  - addCast(@sam)
  - setTags({'Sci-fi', 'Space'})
```

#### Service metadata

Metadata can be assigned to every service definition.

Its then possible to fetch the services matching a metadata key.

```yml
@controller.auth.sign_in: Controller\Auth\SignInController(@auth)
  = route: {'GET', 'POST'}, '/signin'
```

The metadata key is always a vector / array so you can add multiple of the same type:

```yml
@controller.auth.sign_in: Controller\Auth\SignInController(@auth)
  = route: {'GET', 'POST'}, '/signin'
  = tag: 'users'
  = tag: 'auth'
```

The elements inside the metadata definition can have named keys:

```yml
@app.bootstrap: Bootstrap()
  = on: 'app.start' call: 'onAppStart'
```

### Imports

Other container files can be imported from the container namespace.

```yml
import config
import app/dashboard
import app/user
import app/shop
```

### Overriding 

Services and Parameters have been explicit overwritten if they have already been defined.

```
:ship: 'Star Destroyer'

override :ship: 'X-Wing'
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
