# ClanCats Container Usage

This document focuses more on the public API of the default container instance. 

For more basic usage check:

 * [Getting Started](docs://introduction/getting-started)
 * [Container Basics](docs://usage/container)

## Parameters

Think of parameters as mostly _scalar_ configuration values that can be injected inside your dependencies.

### Construct with parameters

[~ PHPDoc](/src/Container.php#__construct)

### Getting a parameter

[~ PHPDoc](/src/Container.php#getParameter)

### Setting a parameter

[~ PHPDoc](/src/Container.php#setParameter)

### Check if parameter exists

[~ PHPDoc](/src/Container.php#hasParameter)

## Services

### Getting a service

[~ PHPDoc](/src/Container.php#get)

### Available services

[~ PHPDoc](/src/Container.php#available)

### Has service

[~ PHPDoc](/src/Container.php#has)

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

### Remove service

[~ PHPDoc](/src/Container.php#remove)

### Release service

[~ PHPDoc](/src/Container.php#release)

### Is service resolved

[~ PHPDoc](/src/Container.php#isResolved)

### Register a service provider

[~ PHPDoc](/src/Container.php#register)

### Get service resolver type

[~ PHPDoc](/src/Container.php#getServiceResolverType)
