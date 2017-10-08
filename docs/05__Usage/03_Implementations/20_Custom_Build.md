# Container Factory / Container Files 

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder) use ($autoloader)
{
    $namespace = new \ClanCats\Container\ContainerNamespace([
        'foo/bar' => __DIR__ . '/container/bar.container',
    ]);
    $namespace->bindComposer($autoloader);

    // parse and 
    $builder->addArray($namespace->parse(__DIR__ . '/app.container'));
});

```