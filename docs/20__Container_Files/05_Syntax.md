# Container File Syntax

Container files are written in very simple meta language.

## Types

The language supports the following scalar types:

 * **strings** Single and double quoted. 
   `'hello'` & `"world"`
 * **numbers** float / double, int.
 	`3.14`, `42`
 * **booleans** `true` and `false`.
 * **null**
    `null`

## Parameters

Parameters or configuration values can also be defined inside the container files. Currently its only possible to assign scalar values to a parameter. Array will probably follow in a future release.

A parameter is always prefixed with a `:` character.

```yml
:database.hostname: "production.db.example.com"
:database.port: 7878
:database.cache: true
```

## Service Defintion

A service definition is always named and must be prefixed with a `@` character. 

```yml
# <service name>: <class name>
@log.adapter: FileAdapter
```

The class name can contain the full namespace.

```yml
@log.adapter: Acme\Log\FileAdapter
```

When having really long namespaces this can get messy. Just like in PHP you can `use` namespaces.

```yml
use Acme\Log\FileAdapter

@log.adapter: FileAdapter
```

To keep things clean you can `use` multiple classes from one namespace just like in PHP7. _(Note the diffrent braces.)_

```yml
use Acme\Log\(
	Logger,
	FileAdapter
)

@log: Logger
@log.adapter: FileAdapter
````

### Constructor

Constructor arguments can be passed after the class name. 

```yml
@dude: Person("Jeffery Lebowski")
```