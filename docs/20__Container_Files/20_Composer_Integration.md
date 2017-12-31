# Composer Integration

It is possible to import container files from vendor packages through composer.

## Importing container files from packages

This is done in two steps.

### 1. Setup composer.json

First we need to tell composer to generate a container file map of our pacakages everytime we dump the autoloader:

```json
{
    "scripts": {
        "post-autoload-dump": [
            "ClanCats\\Container\\ComposerContainerFileLoader::generateMap"
        ]
    }
}
```

This will generate a `container_map.php` file in your vendor directory.

### 2. Setup the container factory.

Tell the container namespace where to look for the generated `container_map.php` file.

```php
$namespace->importFromVendor(__DIR__ . '/vendor');
```

## Exposing container files to composer 

The otherway around, when you want to add container files to your library / pacakage.

The import name of the container files is dependet on your composer package. You will be able to use multiple container files but you have to define the main one.

let's think of a package structure as follows:

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
            "@main": "package.ctn"
            "config": "config.ctn"
        }
    }
}

```

`@main` simply indicates our main container file which will recieve the import name of the package itself.

So inside a project that requires `acme/mypackage` and imports the vendor map we can import the main container file of `acme/mypackage` like this:


```yml
import acme/mypackage
```

The config container file can be imported like so:

```
import acme/mypackage/config
```
