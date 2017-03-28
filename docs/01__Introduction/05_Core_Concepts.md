# Core Concepts / Terminology

Just so that we talk about the same thing let me explain some keywords that are all over this documentation and what I mean with them.

## A Service

A service is simply a fancy name for a PHP object doing a specifc task and can be required at multiple locations in your application. There is absolutly nothing special about it, so why should you care? A service architecture makes you seperate your applications functionality into small focused chunks. Because a service should only serve one specify purpose he is then easier to test and replace if needed. 

https://en.wikipedia.org/wiki/Service-oriented_architecture 

A perfect example would be a logger:

```php
class Logger 
{
    public function log(string $message) : void {
        file_put_contents(__DIR__ . '/var/app.log', time() . ' - ' . $message, FILE_APPEND);
    }
}
```

Now lets say we want to be able to configure what happens with the logs. So we create a handler interface for our `Logger`.

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

This way the API for the logger stays the same no matter how the logs are handled.

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

Putting everything together leaves us with this:

```php
$fileLogger = new FileLogHandler(__DIR__ . '/var/app.log');
$logger = new Logger($fileLogger);
$logger->log('Hello fellow humans.');
```

Doesnt feel very convenient right? That brings us to the next point.


## Service Container

A _Service Container_ or _Dependency Injection Container_ manages your services and their creation. By telling the container which services depend on which other services and parameters, your building a graph that the service container is able to resolve. Most containers just like this one will store the created instance so the next time the same service is requested it won't be reconstructed. 

So in case of the example above we can bind our logger service to the container once:

```php
$container->bindClass('handler.file', FileLogHandler::class, [__DIR__ . '/var/app.log']);
$container->bindClass('logger', Logger::class, ['@handler.file']);
```

And retrieve the logger instance everywhere in your application from your container:

```php
$container->get('logger')->log('Hello fellow robots.');
```

## Service Definition

A service definition is a description of the service (What class name, the required arguments ect.). It is what gets binded to the container and contains Basically all information needded to construct a instance or object of the relevant class. 

```php
$definition = new ServiceDefinition(FileLogHandler::class, ['/path/to/some.log']);
```

> Note: More about [Serivce Definitions](/container/master/usage/service-definitions)

## Service Factory

A service factories job is to create the actual instance of the service. The default service factory extends the `ServiceDefinition` and is therefor able to construct the above definition.

```php
$factory = new ServiceFactory(FileLogHandler::class, ['/path/to/some.log']);
$fileLogger = $factory->create();
```

> Note: More about [Serivce Facatories](/container/master/service-binding/service-factories)
