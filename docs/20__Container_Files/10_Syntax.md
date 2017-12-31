# Container File Syntax

Container files are written in very simple meta language.

## Types

The language supports the following scalar types:

 * **Strings** Single and double quoted. <br>
   `'hello'` & `"world"`
 * **Numbers** float / double, int. <br>
    `3.14`, `42`
 * **Booleans** <br>
 	`true` and `false`.
 * **Null** <br>
    `null`
 * **Arrays** list and associative. <br>
   `{'A', 'B', 'C'}`, `{'A': 10, 'B': 20}`

### Numbers

Container files do not differenciate between diffrent number types because it would be an unessesary overhead, we forward that job directly to PHP.

```
42 # Int
42.01 # Float
-42.12345678912345 # Double
```

That means that also the floating point precision is handled by PHP. All values are interpreted means large doubles might be stored rounded.

### Strings

Strings must always be encapsulated with a single `'` or double `"` quote. This serves mainly a comfort purpose when having many quotes inside your string not having to escape them all.

Escaping of special characters works just the usual way. 

```
:say: 'Hello it\'s me!'`
```

Beloved or Hated emojis will also work just fine. 

```
:snails: '🐌🐌🐌'
```

### Booleans and Null

There is not much to say about them:

```
:nothing: null
```

```
:positive: true
:negative: false
```


### Arrays

Its important to notice that all arrays are internally associative. When defining a simple list the associative key is automatically generated and represents the items index.

This means that the array `{'A', 'B'}` equals `{0: 'A', 1: 'B'}`.

Arrays can be defined multidimensional:

```yml
{
	'title': 'Some catchy title with Star Wars',
	'tags': {'top10', 'movies', 'space'},
	'body': 'Lorem ipsum ...',
	'comments': 
	{
		{
			'text': 'Awesome!',
			'by': 'Some Dude',
		}
	}
}
```

## Parameters

Parameters or configuration values can also be defined inside the container files. 

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