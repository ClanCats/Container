# Service Definitions

A service definition acts as a simple description of a service. But it does not hold container relevant informations like the alias name or if the service will be shared or not.

## Base implementation

This package comes already with an implementation of the `ServiceDefinitionInterface` simply called `ServiceDefinition`.

### Constructor

Create a new service definition:

```php
use ClanCats\Container\ServiceDefinition;

$logger = new ServiceDefinition(MyLogger::class, [__DIR__ . '/to/my/file.log']);
```

[~ PHPDoc](/src/ServiceDefinition.php#__construct)

#### Static factory

There is also a static method to construct new service definition instance allowing a more expressive syntax.

[~ PHPDoc](/src/ServiceDefinition.php#for)

### Construct from array

[~ PHPDoc](/src/ServiceDefinition.php#fromArray)

### Adding arguments 

You can pass additional constructor arguments any time:

```php
$QA = new ServiceDefinition(QA::class);

$QA
    ->addRawArgument('The Answer to the Ultimate Question of Life, The Universe, and Everything.');
    ->addRawArgument(42)
    ->addDependencyArgument('database')
    ->addParameterArgument('priority.default');
```

[~ PHPDoc](/src/ServiceDefinition.php#addRawArgument)
[~ PHPDoc](/src/ServiceDefinition.php#addDependencyArgument)
[~ PHPDoc](/src/ServiceDefinition.php#addParameterArgument)

### Get all arguments

```php
$QA->getArguments(); // ServiceArguments instance
```


[~ PHPDoc](/src/ServiceDefinition.php#getArguments)

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
