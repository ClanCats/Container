# Service Arguments

There are currently 3 types of service arguments available. 

 1. **Raw arguments**<br>
    Simple scalar data. (`strings`, `numbers`, `booleans`)<br>

 2. **Dependencies**<br>
    In other words a reference to another service.

 3. **Parameters**<br>
    A reference to a value from a container parameter.

These types are also defined as constants in the `ServiceArguments` class:

```php
ServiceArguments::RAW
ServiceArguments::DEPENDENCY
ServiceArguments::PARAMETER
```

## Constructor

[~ PHPDoc](/src/ServiceArguments.php#__construct)

The constructor takes in an array, to allow an easier / lazier way to define the arguments:

```php
use ClanCats\Container\ServiceArguments;

$arguments = new ServiceArguments(['Hello', 'World']);
```

When you want do define a dependency in the array manner you can simply prefix the dependencies name with an `@` char.

```php
$mailerArguments = new ServiceArguments(['@mailer.smtp', '@queue']);
```

Same goes for parameters just with a `:` character.

```php
$smtpArguments = new ServiceArguments([
	':smtp.host',
	':smtp.port',
	...
]);
```

**But what if I need a string that starts with an `@`??**

The construcotr makes use of the `addArgumentsFromArray` method. You are still able to define the arguments directly.

```php
$onlyRawArgs = (new ServiceArguments())
	->addRaw('@this is still a string')
	->addRaw(':nope not a parameter');
```

### Static constructor

[~ PHPDoc](/src/ServiceArguments.php#from)


## Adding Arguments

### Raw

```php
$args = (new ServiceArguments())
	->addRaw('a string')
	->addRaw(42);
```

[~ PHPDoc](/src/ServiceArguments.php#addRaw)

### Dependency

```php
$args = (new ServiceArguments())
	->addDependency('logger');
```

[~ PHPDoc](/src/ServiceArguments.php#addDependency)

### Parameter

```php
$args = (new ServiceArguments())
	->addParameter('auth_token');
```

[~ PHPDoc](/src/ServiceArguments.php#addParameter)

### From array

[~ PHPDoc](/src/ServiceArguments.php#addArgumentsFromArray)

### get all

[~ PHPDoc](/src/ServiceArguments.php#getAll)

## Resolving

[~ PHPDoc](/src/ServiceArguments.php#resolve)

