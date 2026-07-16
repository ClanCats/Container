# Parsing

Writing `.ctn` files is only half the story — something has to read them. The `ContainerNamespace` is the object that parses container files and collects the parameters, services, aliases and generics they declare. A parsed namespace is then handed to the [container builder](/usage/implementations/custom-build) to compile the final container class.

::: tip
If you just want the quickest path from files to a working container, follow [Getting Started](/introduction/getting-started) and the [Default implementation](/usage/implementations/default) — they wrap all of the below in a `ContainerFactory`. This page documents the parsing layer itself.
:::

## The namespace

A namespace maps **import names** to file paths and stores everything parsed so far.

```php
use ClanCats\Container\ContainerNamespace;

$namespace = new ContainerNamespace([
    'config' => __DIR__ . '/app/config.ctn',
    'services' => __DIR__ . '/app/services.ctn',
]);
```

The keys (`config`, `services`) are the names other files use in an [`import`](/container-files/imports) statement — they are **not** file paths.

## Parsing a file

`parse()` reads a `.ctn` file, lexes and parses it, then interprets the result into the namespace. Any `import` statements inside the file are resolved against the namespace's name → path map.

```php
$namespace->parse(__DIR__ . '/app/services.ctn');
```

## Registering a whole directory

`importDirectory()` recursively scans a directory for `.ctn` files and registers each one under an import name derived from its path (relative, forward-slashed, without the extension). This saves you from listing every file by hand.

```php
// register every .ctn file under app/ as an importable name
$namespace->importDirectory(__DIR__ . '/app');

// optionally prefix the generated import names
$namespace->importDirectory(__DIR__ . '/packages/shop', 'shop');

// the file extension can be customized (defaults to '.ctn')
$namespace->importDirectory(__DIR__ . '/app', '', '.ctn');
```

## How imports resolve

When the interpreter encounters `import app/user`, it asks the namespace for the code registered under that name:

- `has(string $name): bool` — is the name known?
- `getPath(string $name): string` — the file path it maps to.
- `getCode(string $name): string` — the raw source for that name.

This indirection is what lets packages ship container files under stable import names regardless of where they physically live on disk (see [Composer Integration](/container-files/composer-integration) and [Custom Namespace](/container-files/custom-namespace)).

## Reading what was parsed

After parsing you can inspect everything the namespace collected:

```php
$namespace->getParameters(); // ['database.hostname' => '...', ...]
$namespace->getServices();   // ['repository.users' => ServiceDefinition, ...]
$namespace->getAliases();    // ['pipeline.queue' => 'queue.redis', ...]
$namespace->getGenerics();   // ['repository' => GenericDefinitionNode, ...]
```

Each has a matching `has*()` check (`hasParameter()`, `hasService()`, `hasAlias()`, `hasGeneric()`). These getters are exactly what the [container builder](/usage/implementations/custom-build) consumes via `importNamespace()` to generate the compiled container.
