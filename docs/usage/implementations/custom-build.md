# Custom Build Implementation

Don't want to use [container files](/container-files/introduction) but still want to **compile** the container into a fast generated class? Then this implementation is for you. Instead of parsing `.ctn` files, you feed service definitions to the [`ContainerBuilder`](/usage/lifecycle) yourself — from any source you like (a JSON file, a config array, a database).

This gives you the same compiled output as the [Default implementation](/usage/implementations/default), just with your own definition format.

## Example: building from a JSON document

Say you keep your service definitions in a JSON file:

```json
{
    "session": {
        "class": "Session",
        "arguments": [ "@session.adapter", ":session.lifetime" ]
    },
    "session.adapter": {
        "class": "DatabaseSession",
        "arguments": ["@pdo"]
    }
}
```

Load it and hand the decoded array to the builder inside the factory callback:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('JsonContainer', function($builder)
{
    // decode the JSON file into an associative array
    $services = json_decode(file_get_contents(__DIR__ . '/services.json'), true);

    // hand the whole array to the builder
    $builder->addArray($services);
});
```

Now the services resolve just like any other:

```php
$session = $container->get('session'); // Session instance, with its dependencies injected
```

## The array shape

`ContainerBuilder::addArray()` expects a map of `service name → definition`. Each definition supports these keys:

| Key | Type | Description |
| --- | --- | --- |
| `class` | `string` | **Required.** Fully qualified class name to instantiate. |
| `arguments` | `array` | Constructor arguments. Prefix `@` for a service dependency, `:` for a [parameter](/usage/parameters); anything else is a raw value. |
| `calls` | `array` | Method calls after construction. Each entry is `['method' => 'setX', 'arguments' => [...]]`. |
| `shared` | `bool` | Whether the service is [shared](/usage/services) (default `true`). |

A fuller definition:

```php
$builder->addArray([
    'logger' => [
        'class'     => '\Acme\Logger',
        'arguments' => ['@handler.file'],
        'calls'     => [
            ['method' => 'setLevel', 'arguments' => ['debug']],
        ],
        'shared'    => true,
    ],
]);
```

This mirrors exactly what a `.ctn` service definition produces — see [Container Files › Services](/container-files/services) for the equivalent syntax. Under the hood each entry becomes a [`ServiceDefinition`](/advanced/service-definitions).

::: tip
To add a single service programmatically instead of a whole array, use `$builder->add($name, $class, $arguments, $shared)`.
:::
