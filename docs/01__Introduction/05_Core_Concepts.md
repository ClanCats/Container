# Core Concepts / Terminology

Just so that we are talking about the same thing, let me explain some keywords that come up all through this documentation and what I mean by them.

## A Service

A service is simply a fancy name for a PHP object doing a specifc task and can be required at multiple locations in your application. There is absolutly nothing special about it, so why should you care? A service architecture makes you seperate your applications functionality into small focused chunks. Because a service should only serve one specific purpose, it is much easier to test and replace if needed. This, when done right, results in a easy maintainable application that scales very well (im not talking about performance here) with the complexity of the app.

https://en.wikipedia.org/wiki/Service-oriented_architecture 

If this all still sounds like a lot of bla bla, don't worry the following example will help.

```php
class Logger 
{
    public function log(string $message) : void {
        file_put_contents(__DIR__ . '/var/app.log', time() . ' - ' . $message, FILE_APPEND);
    }
}
```

The `Logger` above then already classifies as a service, it only serves one purpose and that is to take in log messages and do something with them. To be more specific it will append them into a fix defined file.

But wait. What do you do if the situation occurs, when you just want to print out the log messages.  Well you could take in another parameter something like `public function log(string $message, bool $printMessage) : void` but that just doesnt feel right.. I mean what if you want to add another option to send the message via UDP?

To solve this in a DI manner we need to make the `Logger` class more stupid:

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

So our logger doesn't know what to do anymore with the logs, he just forwards them to a handler. This way the API for the logger stays the same no matter how the logs are handled.

Every service should focus on something really specific. So we can now create multiple handlers for everything we need:

```php
class PrintLogHandler implements LogHandler 
{
    public function store(int $time, string $message) : void {
        echo "[$time] – $message\n";
    }
}
```

To fulfill the file logger example:

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

A _Service Container_ or _Dependency Injection Container_ manages your services and their creation. By telling the container which services depend on which other services and parameters, your building a graph that the service container is able to resolve. Most containers like this one, will store the created instance. So the next time the same service is requested it won't be reconstructed. 

So in the case of the example above we can bind our logger service to the container once:

```php
$container->bindClass('handler.file', FileLogHandler::class, [__DIR__ . '/var/app.log']);
$container->bindClass('logger', Logger::class, ['@handler.file']);
```

And retrieve the logger instance everywhere in your application from your container:

```php
$container->get('logger')->log('Hello fellow robots.');
```

## Service Definition

A service definition acts as a simple description of the service. It does not hold _container_ relevant informations like the alias name or if the service will be shared or not.

Usually the service definition holds the following information:

 * class name
 * constructor arguments
 * inital method calls
 * property assignments _(*currently not implemented)_

```php
$definition = new ServiceDefinition(FileLogHandler::class)
    ->addRawArgument(__DIR__ . '/var/log/application.log');
```

> Note: More about [Serivce Definitions](docs://advanced/service-definitions)

## Service Factory

A service factory's job is to create an actual instance of the service. The default service factory extends the `ServiceDefinition` and is therefor able to construct the above definition.

```php
$factory = new ServiceFactory(FileLogHandler::class)
    ->addRawArgument(__DIR__ . '/var/log/application.log');

$logger = $factory->create(); // FileLogHandler instance
```

> Note: More about [Serivce Facatories](docs://advanced/service-factories)
