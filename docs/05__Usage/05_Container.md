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

```php
$container->getParameter('cron.idle.timeout', 3600);
```

[~ PHPDoc](/src/Container.php#getParameter)

### Setting a parameter

I like to think of parameters as configuration values that are more or less scalar, but there is no limitation on what a parameter can be. You could assign objects as parameters but that does not mean you should.

```php
$container->setParameter('session.cookie.name', 'mysessiontoken');
```

```php
$container->setParameter('available.languages', ['de', 'en', 'it', 'fr']);
```

[~ PHPDoc](/src/Container.php#setParameter)

### Check if parameter exists

Sometimes you just need to know if a parameter exists. If a parameter is null it still exists!

[~ PHPDoc](/src/Container.php#hasParameter)

## Getting services / status

### Getting a service

Gets the service with the given name from the container. This method will also construct the service if it has not been requested before or is of type prototype.

```php
$container->get('repository.comment');
```

The name `container` is the only reserved one, and will always refer to the container itself;

```php
$container->get('container'); // === $container
```

If a service does not exist the container will throw an `UnknownServiceException` if it is requested. So you should either catch the exeption or check if the servie exists beforehand if you don't trust your dependency tree.

```php
if ($container->has('not.sure.if.exists')) {
	$container->get('not.sure.if.exists'); // do something with it
}
```

```php
try {
	$container->get('not.sure.if.exists');
} catch(ClanCats\Container\Exceptions\UnknownServiceException $e) {
	// do something its not there.
}
```

[~ PHPDoc](/src/Container.php#get)

### Available services

Working on debugging tools and just need to know what services would be available?

```php
echo implode(', ', $container->available());
```

[~ PHPDoc](/src/Container.php#available)

### Has service

[~ PHPDoc](/src/Container.php#has)

### Is service resolved

If you need to know if a service has already been loaded / requested / resolved you can make use of the `isResolved` method. This is also mainly usefull for profiling and debugging.

```php
foreach($container->available() as $serviceKey) {
	echo "@{$serviceKey}: " . ($conatiner->isResolved(serviceKey) ? 'yes' : 'no') . PHP_EOL;
}
```

[~ PHPDoc](/src/Container.php#isResolved)

## Binding / Setting Services

### Bind service

[~ PHPDoc](/src/Container.php#bind)

#### Bind service by class

[~ PHPDoc](/src/Container.php#bindClass)

#### Bind service factory

[~ PHPDoc](/src/Container.php#bindFactory)

#### Bind shared service factory

[~ PHPDoc](/src/Container.php#bindFactoryShared)

### Set service

[~ PHPDoc](/src/Container.php#set)

## Releasing / Removing services

### Remove service

[~ PHPDoc](/src/Container.php#remove)

### Release service

[~ PHPDoc](/src/Container.php#release)


