# Installation

Install the container with [Composer](https://getcomposer.org/):

```bash
composer require clancats/container
```

## Requirements

 * **PHP ≥ 7.4** (tested on 7.4, 8.0, 8.1).
 * No other dependencies — the package is dependency-free.

## A writable cache directory

If you use the recommended [Default implementation](/usage/implementations/default), the container is compiled into a generated PHP class that is written to a cache directory. Make sure the directory you hand to the `ContainerFactory` exists and is writable by PHP:

```
cache/   # must be writable
```

The [Dynamic implementation](/usage/implementations/dynamic) does not compile anything and therefore needs no cache directory.

Next, head to [Getting Started](/introduction/getting-started).
