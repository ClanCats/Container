# The Container Lifecycle

This library's headline feature is **compilation**: the expensive work of reading your `.ctn` files and resolving the dependency graph happens once, at build time, and produces a plain PHP class. At runtime you just include that class and resolve services from it — there is almost no overhead.

Understanding this pipeline explains *why* the API is shaped the way it is.

## The pipeline

```
  app.ctn (source text)
        │
        ▼
   ContainerLexer        tokenizes the .ctn source
        │
        ▼
      Parser             builds an AST of Nodes
        │
        ▼
 ContainerInterpreter    walks the AST, fills a…
        │
        ▼
 ContainerNamespace      the "pot" of parsed parameters + service definitions
        │  (importNamespace)
        ▼
 ContainerBuilder        code-generates a Container subclass…
        │  (generate)
        ▼
 Generated PHP class     one resolver method per service, written to cache/
        │  (require + new)
        ▼
   Container instance    fast runtime resolution — get(), has(), …
```

Everything above the generated class is **build-time** and runs only when the cache is (re)built. Everything below is **runtime**.

## Who drives it

For the recommended setup you never touch the lexer, parser or interpreter directly. The [`ContainerFactory`](/usage/implementations/default) orchestrates the whole thing:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('AppContainer', function($builder) {
    $namespace = new \ClanCats\Container\ContainerNamespace();
    $namespace->parse(__DIR__ . '/app.ctn');   // source → namespace
    $builder->importNamespace($namespace);      // namespace → builder
});
```

`create()` does the following:

1. If a class named `AppContainer` is already loaded, it returns a new instance immediately.
2. Otherwise it looks for a cached file (`cache/AppContainer.php`).
3. If that file is **missing** — or [debug mode](#debug-mode) is on — it runs your callback, code-generates the class and writes it to the cache directory.
4. It `require`s the cache file and returns `new AppContainer($initialParameters)`.

So the parsing/building steps only ever run when the cache file is absent.

## Debug mode

Because `create()` rebuilds only when the cache file is missing, editing an `.ctn` file during development would not take effect until you delete the cache. **Debug mode** forces a rebuild on every call:

```php
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache', true); // debug on
```

Turn it off in production so the cached class is reused.

## Where to go next

 * [Choosing an Implementation](/usage/implementations/choosing) — compiled vs. dynamic vs. custom build.
 * [Using the Container](/usage/container) — the runtime API that is identical across all implementations.
 * [Container Files](/container-files/introduction) — the `.ctn` syntax that feeds this pipeline.
