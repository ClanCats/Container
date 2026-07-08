# Container File Syntax

Container files are written in a very simple meta language.

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

Container files do not differentiate between different number types because it would be an unnecessary overhead, we forward that job directly to PHP.

```
42 # Int
42.01 # Float
-42.12345678912345 # Double
```

That means that also the floating point precision is handled by PHP. All values are interpreted means large doubles might be stored rounded.

### Strings

Strings must always be encapsulated with a single `'` or double `"` quote. This serves mainly a comfort purpose when having many quotes inside your string not having to escape them all.

Escaping of special characters works just the usual way. 

```
:say: 'Hello it\'s me!'`
```

Beloved or Hated emojis will also work just fine. 

```
:snails: '🐌🐌🐌'
```

Repeated spaces and tabs are always collapsed to a single space, and this also applies inside a string. This keeps container files tidy regardless of how they're indented, but it means you can't rely on whitespace to format text you store, e.g. a multi-line example embedded in a description.

```
:example: "a  b" # becomes "a b"
```

If you need to preserve formatting exactly as written, use a heredoc block instead. This is an explicit opt-out of the whitespace collapsing described above.

```
:example: <<<EOT
    {
        "foo": "bar"
    }
EOT
```

A heredoc starts with `<<<` followed by a tag name, and ends with a line containing only the matching tag. The tag can be any combination of letters, digits and underscores, and everything in between is kept exactly as written, including indentation and repeated spaces. Unlike PHP's heredoc syntax there is no automatic indentation stripping, the closing tag must be flush against the start of its line.

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

It's important to notice that all arrays are internally associative. When defining a simple list the associative key is automatically generated and represents the index of the item.

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

## Parameters

Parameters or configuration values can also be defined inside the container files. 

A parameter is always prefixed with a `:` character.

```yml
:database.hostname: "production.db.example.com"
:database.port: 7878
:database.cache: true
```

## Service Definition

A service definition is always named and must be prefixed with a `@` character. 

```yml
# <service name>: <class name>
@log.adapter: FileAdapter
```

The class name can contain the full namespace.

```yml
@log.adapter: Acme\Log\FileAdapter
```
### Constructor

Constructor arguments can be passed after the class name. 

```yml
@dude: Person("Jeffery Lebowski")
```

#### Referenced arguments

Arguments can reference a parameter or service.

```yml
:name: 'Jeffery Lebowski'

@dude: Person(:name)
```

```yml
@mysql: MySQLAdapter('localhost', 'root', '')

@repository.posts: Repositories/Post(@mysql)
```

### Method calls

Method calls can be assigned to a service definition.

```yml
@jones: Person('Duncan Jones')
@sam: Person('Sam Rockwell')

@movie.moon: Movie('Moon')
  - setDirector(@jones)
  - addCast(@sam)
  - setTags({'Sci-fi', 'Space'})
```

### Service metadata

Metadata can be assigned to every service definition.

Its then possible to fetch the services matching a metadata key.

```yml
@controller.auth.sign_in: Controller\Auth\SignInController(@auth)
  = route: {'GET', 'POST'}, '/signin'
```

The metadata key is always a vector / array so you can add multiple of the same type:

```yml
@controller.auth.sign_in: Controller\Auth\SignInController(@auth)
  = route: {'GET', 'POST'}, '/signin'
  = tag: 'users'
  = tag: 'auth'
```

The elements inside the metadata definition can have named keys:

```yml
@app.bootstrap: Bootstrap()
  = on: 'app.start' call: 'onAppStart'
```

### Service Updates

It is possible to update already defined services with more construction calls and metadata. 
This is quite handy to organize large amount of dependencies with a dynamic lookups.

You could for example define your logger in one file.

```yml
@logger.main: Acme\Logger
```

And add observers using a construction call where you need them.

```yml
@logger.observers.email_devs: Acme\EmailLogObserver('dev@example.com')
@logger.observers.email_support: Acme\EmailLogObserver('support@example.com')

@logger.main
  - addObserver(@logger.observers.email_devs)
  - addObserver(@logger.observers.email_support)
```

## Imports

Other container files can be imported from the container namespace.

```yml
import config
import app/dashboard
import app/user
import app/shop
```

## Overriding 

Services and Parameters have been explicit overwritten if they have already been defined.

```
:ship: 'Star Destroyer'

override :ship: 'X-Wing'
```
