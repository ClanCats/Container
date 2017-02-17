# Service Binding Basics

A Container instance compiled or not, is always able to bind new or override existing services dynamically during your application runtime. This also allows us to define `Closure` callbacks as service factories.

Keep in mind that in the below examples following namespaces are used: 

```php
use ClanCats\Container\{
	Container,
	ServiceFactory	
};
```

## Bind method

The Containers `bind` method acts as a shortcut and supports three diffrent argument types:

 * `ServiceFactoryInterface` instance.
 * `Closure` callback.
 * Classname represented as string.

[~ PHPDoc](/src/Container.php#bind)

### Bind ServiceFactoryInterface 

The `ServiceFactoryInterface` demnads only a `create` method to retrieve the expected service. This package comes with a prebuild `ServiceFactory` class allowing you to construct a service from a `ServiceDefinition` or basically by his classname.

> Note: Check [Service Factories](service-factories) for more.

```php
$sessionFactory = new ServiceFactory('\\Acme\\Session', ['@session.provider.mysql']);

$container->bind('session', $sessionFactory);
```