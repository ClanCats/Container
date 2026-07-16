# Services

Services are the whole reason the container exists. A service is a named object your application is built from — a database connection, a mailer, a repository — and instead of constructing it with `new` and threading its dependencies through by hand, you declare it once and let the container assemble it for you.

A service definition is always named and prefixed with a `@` character:

```ctn
# <service name>: <class name>
@log.adapter: FileAdapter
```

The class name can contain the full namespace:

```ctn
@log.adapter: Acme\Log\FileAdapter
```

## Constructor

Most objects need something to be built. Constructor arguments are passed after the class name, exactly as you'd pass them to `new`:

```ctn
@mailer: Acme\Mailer('no-reply@example.com')
```

Any [value type](/container-files/values) can be passed as an argument.

### Referenced arguments

The interesting arguments aren't plain values, though — they're your *other* services and your configuration. An argument may reference a [parameter](/container-files/parameters) (with `:`) or another service (with `@`):

```ctn
:mailer.from: 'no-reply@example.com'

@mailer: Acme\Mailer(:mailer.from)
```

```ctn
@database: Acme\Database('localhost', 'root', '')

@repository.users: Acme\UserRepository(@database)
```

When you ask for `repository.users`, the container sees it depends on `@database`, builds the database first, and hands it over — the dependency graph is resolved for you.

## Method calls

Not everything can go through the constructor. Sometimes an object needs a few calls made on it once it exists — a setter here, a handler registered there. Method calls do exactly that: they run after construction, in the order written, and are prefixed with `-`.

```ctn
@transport.smtp: Acme\SmtpTransport('smtp.example.com', 587)

@mailer: Acme\Mailer('no-reply@example.com')
  - setTransport(@transport.smtp)
  - setRetries(3)
  - addRecipientTags({'customers', 'staff'})
```

Method call arguments follow the same rules as constructor arguments — raw values, parameter references and service references are all allowed.

## Service updates

As an application grows, the calls a service needs are often decided far from where the service itself is defined. You can **update** an already-defined service with more method calls and metadata, which is handy for organizing a large graph or wiring up dynamic collections.

You might define your logger in one file:

```ctn
@logger.main: Acme\Logger
```

…and then, from wherever an observer belongs, add it with a method call — just repeat the service name without a class name, followed by the additional calls:

```ctn
@logger.observers.email_devs: Acme\EmailLogObserver('dev@example.com')
@logger.observers.email_support: Acme\EmailLogObserver('support@example.com')

@logger.main
  - addObserver(@logger.observers.email_devs)
  - addObserver(@logger.observers.email_support)
```

## Aliases

Sometimes you want a stable, intention-revealing name for a service without committing every caller to a concrete class. A service can be **aliased** to another by pointing its definition at an existing `@service` reference; resolving the alias returns the very same instance as its target.

```ctn
@queue.redis: Acme\Queue\RedisQueue(@redis)

# `@pipeline.queue` now resolves to the very same instance as `@queue.redis`
@pipeline.queue: @queue.redis
```

The payoff is a single point of change: the rest of your app depends on `@pipeline.queue`, and the day you swap Redis for something else, you edit one line — the alias target — and every caller follows.

::: tip
Add [metadata / tags](/container-files/metadata) to any service to look it up by tag at runtime, or replace an existing definition with [`override`](/container-files/imports#overriding).
:::
