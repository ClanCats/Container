# Services

A service is a named object your application is built from. A service definition is always named and must be prefixed with a `@` character.

```ctn
# <service name>: <class name>
@log.adapter: FileAdapter
```

The class name can contain the full namespace.

```ctn
@log.adapter: Acme\Log\FileAdapter
```

## Constructor

Constructor arguments can be passed after the class name.

```ctn
@dude: Person("Jeffery Lebowski")
```

Any [value type](/container-files/values) can be passed as an argument.

### Referenced arguments

Arguments can reference a [parameter](/container-files/parameters) (with `:`) or another service (with `@`).

```ctn
:name: 'Jeffery Lebowski'

@dude: Person(:name)
```

```ctn
@mysql: MySQLAdapter('localhost', 'root', '')

@repository.posts: Repositories\Post(@mysql)
```

## Method calls

Method calls can be assigned to a service definition. They run after construction, in the order written, and are prefixed with `-`.

```ctn
@jones: Person('Duncan Jones')
@sam: Person('Sam Rockwell')

@movie.moon: Movie('Moon')
  - setDirector(@jones)
  - addCast(@sam)
  - setTags({'Sci-fi', 'Space'})
```

Method call arguments follow the same rules as constructor arguments — raw values, parameter references and service references are all allowed.

## Service updates

It is possible to update an already defined service with more method calls and metadata. This is quite handy to organize a large amount of dependencies with dynamic lookups.

You could for example define your logger in one file.

```ctn
@logger.main: Acme\Logger
```

And add observers using a method call where you need them — just repeat the service name without a class name, followed by the additional calls.

```ctn
@logger.observers.email_devs: Acme\EmailLogObserver('dev@example.com')
@logger.observers.email_support: Acme\EmailLogObserver('support@example.com')

@logger.main
  - addObserver(@logger.observers.email_devs)
  - addObserver(@logger.observers.email_support)
```

## Aliases

A service can be aliased to another service by pointing the definition at an existing `@service` reference. Resolving the alias returns the target service.

```ctn
@queue.redis: Acme\Queue\RedisQueue(@redis)

# `@pipeline.queue` now resolves to the very same instance as `@queue.redis`
@pipeline.queue: @queue.redis
```

::: tip
Add [metadata / tags](/container-files/metadata) to any service to look it up by tag at runtime, or replace an existing definition with [`override`](/container-files/imports#overriding).
:::
