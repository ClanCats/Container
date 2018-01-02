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

# Below docs are not done...

----

```php
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

// bind a "shuttle" spaceship, inject the pulsedrive and 
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