# Composer Integration

When you pull in a Composer package that provides its own services — a mailer, a database layer, a whole bundle — you'd rather not copy its service definitions into your own `.ctn` files by hand. Container files integrate with Composer so that a package can ship its `.ctn` files and you can `import` them by name, just like your own.

This page covers both sides of that: [importing container files](#importing-container-files-from-packages) that a package provides, and [exposing your own](#exposing-container-files-to-composer) when you're the one writing the package.

## Importing container files from packages

Wiring this up takes two steps: tell Composer to build a map of the container files your packages provide, then point the namespace at that map.

### 1. Setup composer.json

First we need to tell composer to generate a container file map of our packages every time we dump the autoloader:

```json
{
    "scripts": {
        "post-autoload-dump": [
            "ClanCats\\Container\\ComposerContainerFileLoader::generateMap"
        ]
    }
}
```

Now, every time composer dumps the autoloader (`composer install`, `composer update`, `composer dump-autoload`), it scans every installed package for an `extra.container` entry (see [below](#exposing-container-files-to-composer)) and writes a `container_map.php` file into your vendor directory. That file simply `return`s an array mapping each exposed import name to the absolute path of its `.ctn` file:

```php
<?php
$vendorDir = __DIR__ . '/';

return [
    'acme/mypackage'        => $vendorDir . 'acme/mypackage/package.ctn',
    'acme/mypackage/config' => $vendorDir . 'acme/mypackage/config.ctn',
];
```

### 2. Setup the container factory.

Tell the container namespace where to look for the generated `container_map.php` file.

```php
$namespace->importFromVendor(__DIR__ . '/vendor');
```

::: tip Verify it worked
Run `composer dump-autoload` and confirm `vendor/container_map.php` exists and lists the packages you expect. If a package is missing, double-check its `extra.container` block. If the file itself is absent, make sure the `post-autoload-dump` script from step 1 is present in the project's own `composer.json`.
:::

## Exposing container files to Composer

Now the other direction: you're writing a library and want the projects that install it to import your services with a single line.

A package may expose as many container files as it likes, and their import names are derived from your Composer package name. One of them is the *main* file — the one consumers get when they import the package by its bare name — and you may add any number of additional named files alongside it.

Let's take a package laid out like this:

```
config.ctn
package.ctn
composer.json
src/
  MyClass.php
```

Inside the `composer.json` file we need to define what container files are available:

```json
{
	"name": "acme/mypackage",
    "extra": {
        "container": {
            "@main": "package.ctn",
            "config": "config.ctn"
        }
    }
}

```

`@main` simply indicates our main container file which will receive the import name of the package itself.

So inside a project that requires `acme/mypackage` and imports the vendor map we can import the main container file of `acme/mypackage` like this:


```ctn
import acme/mypackage
```

The config container file can be imported like so:

```ctn
import acme/mypackage/config
```
