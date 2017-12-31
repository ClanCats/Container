# Basic Container usage

As already stated and in the [Getting started](docs://introduction/getting-started) guide, there are multiple ways to construct an instnace of the service container. But in the end it does not really matter if a container is generated using the factory or is created dynamically, the usage / API of the container instance is the same for all of them.

## Construction and Parameters

The constructor of the container always accepts an inital array of dynamic parameters.

```php
use ClanCats\Container\Container;
$container = Container(['env' => 'production']);
```

This also works if you construct the container instance using a factory:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');
$container = $factory->create('AppContainer', function($builder) {}, ['env' => 'production']);
```

The given inital parameters will be **merged** with already defined parameters. 

### Reading a parameter

This method really does not require to explain alot, thats why im wasting your time with this sentence. 

```php
$container->getParameter('env');
```

You can get a parameter with a fallback value. The fallback value will be returned if the parameter has not been defined. If the parameter is `null` the default value won't be returned.

```
$container->getParameter('cron.idle.timeout', 3600);
```

[~ PHPDoc](/src/Container.php#getParameter)

### Setting a parameter

can be anything

[~ PHPDoc](/src/Container.php#setParameter)

### Check if parameter exists

[~ PHPDoc](/src/Container.php#hasParameter)

### Getting a service

[~ PHPDoc](/src/Container.php#get)
