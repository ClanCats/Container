# Core Concepts

A few words come up again and again throughout this documentation — *service*, *container*, *definition*, *factory*. So that we're always talking about the same thing, let's walk through what each one means. If you already work with dependency injection day to day, feel free to skim; if the terms are new, don't lose heart — a small example makes all of them concrete.

## A Service

A *service* is just a name for a PHP object that does one specific job and may be needed in several places across your application — a logger, a database connection, a mailer. There's nothing special about the object itself, so why bother naming the idea at all?

Because thinking in services nudges you to split your application into small, focused pieces. When an object serves a single purpose, it's far easier to test in isolation and to swap out later. Done consistently, this keeps a growing application maintainable as its complexity increases (we're talking about maintainability here, not raw performance). You can read more about the broader idea under [service-oriented architecture](https://en.wikipedia.org/wiki/Service-oriented_architecture).

If that still sounds abstract, don't worry — the following example makes it concrete.

```php
class Logger 
{
    public function log(string $message) : void {
        file_put_contents(__DIR__ . '/var/app.log', time() . ' - ' . $message, FILE_APPEND);
    }
}
```

That `Logger` already qualifies as a service: it serves one purpose, taking in log messages and doing something with them — here, appending them to a fixed file.

But what happens the day you want to *print* the messages instead of writing them to that file? You could add another argument — `public function log(string $message, bool $printMessage) : void` — but that doesn't feel right, and it won't scale. What about the day after, when you also want to send messages over UDP?

The dependency-injection answer is to make the `Logger` know *less*, not more. Let it forward the work to a separate handler:

```php
interface LogHandler {
    public function store(int $time, string $message) : void;
}

class Logger 
{
    protected $handler;

    public function __construct(LogHandler $handler) {
        $this->handler = $handler;
    }

    public function log(string $message) : void {
        $this->handler->store(time(), $message);
    }
}
```

Now the logger no longer decides *how* logs are stored; it simply forwards them to a handler. Its own API stays the same no matter what happens to the messages downstream.

With each service focused on one specific thing, you're free to write as many handlers as you need. Here's one that prints:

```php
class PrintLogHandler implements LogHandler 
{
    public function store(int $time, string $message) : void {
        echo "[$time] – $message\n";
    }
}
```

And here's the file-based one from before, now as a proper handler:

```php
class FileLogHandler implements LogHandler 
{
    protected $path;

    public function __construct(string $path) {
        $this->path = $path;
    } 

    public function store(int $time, string $message) : void {
        file_put_contents($this->path, $time . ' - ' . $message, FILE_APPEND);
    }
}
```

Wiring it all together by hand looks like this:

```php
$fileLogger = new FileLogHandler(__DIR__ . '/var/app.log');
$logger = new Logger($fileLogger);
$logger->log('Hello fellow humans.');
```

That works, but constructing every object and threading its dependencies through by hand gets tedious fast — and that's exactly the problem the container solves.

## Service Container

A *service container* — also called a *dependency injection container* — manages your services and their creation for you. You describe which services depend on which other services and parameters, and the container treats that as a graph it knows how to resolve. Like most containers, this one caches each instance it builds, so the next time you ask for the same service you get the one you already have rather than a fresh one.

Taking the example above, you describe the logger to the container just once:

```php
$container->bindClass('handler.file', FileLogHandler::class, [__DIR__ . '/var/app.log']);
$container->bindClass('logger', Logger::class, ['@handler.file']);
```

From then on you retrieve the fully-wired logger anywhere in your application, and the container takes care of building `FileLogHandler` and injecting it for you:

```php
$container->get('logger')->log('Hello fellow robots.');
```

## Service Definition

A *service definition* is a plain description of a single service. It deliberately leaves out anything the container owns — the name the service is registered under, or whether it's shared — and describes only how to build the object itself.

A definition usually carries:

 * class name
 * constructor arguments
 * initial method calls
 * property assignments _(*currently not implemented)_

```php
$definition = (new ServiceDefinition(FileLogHandler::class))
    ->addRawArgument(__DIR__ . '/var/log/application.log');
```

::: tip
More about [Service Definitions](/advanced/service-definitions)
:::

## Service Factory

Where a definition only *describes* a service, a *service factory* actually builds it. The default factory extends `ServiceDefinition`, so it understands everything a definition holds and adds the one thing a definition lacks: a `create()` method that turns the description into a real object.

```php
$factory = (new ServiceFactory(FileLogHandler::class))
    ->addRawArgument(__DIR__ . '/var/log/application.log');

$logger = $factory->create(); // FileLogHandler instance
```

::: tip
More about [Service Factories](/advanced/service-factories)
:::


