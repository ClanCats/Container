# Service Definitions

A service definition acts as a simple description of a service. But it does not hold container relevant informations like the alias name or if the service will be shared or not.

This package comes already with an implementation of the `ServiceDefinitionInterface` simply called `ServiceDefinition`.

## Constructor

[~ PHPDoc](/src/ServiceDefinition.php#__construct)

Create a new service definition:

```php
use ClanCats\Container\ServiceDefinition;

$logger = new ServiceDefinition(MyLogger::class);
```

You can also directly pass the constructor arguments as an array:

```php
$logger = new ServiceDefinition(MyLogger::class, ['@some_dependency', ':some_parameter', 42]);
```

Keep in mind when passing arguments as an array prefixing a string with `@` will be interpreted as a dependency and `:` as parameter. This applies everywhere arguments are defined as array. [Service Arguments](docs://usage/arguments)

### Static factory

There is also a static method to construct new service definition instance allowing a more expressive syntax.

[~ PHPDoc](/src/ServiceDefinition.php#for)

### Construct from array

[~ PHPDoc](/src/ServiceDefinition.php#fromArray)

## Constructor Arguments

You can pass additional constructor arguments any time:

```php
$QA = new ServiceDefinition(QA::class);

$QA
    ->addRawArgument('The Answer to the Ultimate Question of Life, The Universe, and Everything.');
    ->addRawArgument(42)
    ->addDependencyArgument('database')
    ->addParameterArgument('priority.default');
```

Using the `arguments` method you can also pass them as an array.

```php
$auth = new ServiceDefinition(MyAuth::class)
    ->arguments([
        '@repository.users',
        ':auth.secret'
    ]);
```

### Add raw argument

[~ PHPDoc](/src/ServiceDefinition.php#addRawArgument)

```php
$def = ServiceDefinition::for('\Acme\SqlConnection')
    ->addRawArgument('localhost')
    ->addRawArgument('root')
    ->addRawArgument('pleaseDontUseRoot');
```

### Add dependency argument

[~ PHPDoc](/src/ServiceDefinition.php#addDependencyArgument)

```php
$def = ServiceDefinition::for('\Acme\Blog\PostRepository')
    ->addDependencyArgument('db.connection');
```

### Add parameter argument

[~ PHPDoc](/src/ServiceDefinition.php#addParameterArgument)

```php
$def = ServiceDefinition::for('\Acme\Session')
    ->addParameterArgument('session.secret');
```

### Get all arguments

[~ PHPDoc](/src/ServiceDefinition.php#getArguments)

```php
$definition->getArguments(); // ServiceArguments instance
```

### Get service class name

[~ PHPDoc](/src/ServiceDefinition.php#getClassName)

## Interface

Any class that will be used as a service definition must implement the `ServiceDefinitionInterface` which requires the following methods:

#### Return the service class name

```php
public function getClassName() : string;
```

---

#### Return the constructor arguments object

```php
public function getArguments() : ServiceArguments;
```

---

#### Return the registered method calls

```php
public function getMethodCalls() : array;
```

The format of the returned array should look like `[string => ServiceArguments]`.
