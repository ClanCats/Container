# Service Arguments

Whenever you define a service programmatically, you need to describe what to feed into its constructor and method calls. Some of those values are plain data, some are other services, and some are configuration parameters — and the container has to tell them apart so it can resolve references before handing them over. `ServiceArguments` is the small wrapper that carries that information.

You will rarely construct it by hand; the [service definition](/advanced/service-definitions) and [factory](/advanced/service-factories) helpers build it for you. But understanding the three argument types explains how the container knows a `@` means "another service" and a `:` means "a parameter."

There are three types of service arguments:

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

So what happens if you genuinely need a raw string that *starts* with an `@` or a `:` — a value that only looks like a reference? The array shorthand would misread it as a dependency or parameter. When that happens, skip the shorthand and add the arguments explicitly instead. The constructor is only a convenience over `addArgumentsFromArray`; you are always free to define each argument directly and say exactly what you mean:

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

