# Values & Types

Every argument, parameter and metadata entry in a container file is one of a small set of value types. The language supports:

 * **Strings** — single and double quoted. <br>
   `'hello'` & `"world"`
 * **Numbers** — float / double, int. <br>
    `3.14`, `42`
 * **Booleans** <br>
     `true` and `false`.
 * **Null** <br>
    `null`
 * **Arrays** — list and associative. <br>
   `{'A', 'B', 'C'}`, `{'A': 10, 'B': 20}`

## Numbers

Container files do not differentiate between different number types because it would be an unnecessary overhead, we forward that job directly to PHP.

```ctn
42 # Int
42.01 # Float
-42.12345678912345 # Double
```

That means that also the floating point precision is handled by PHP. All values are interpreted means large doubles might be stored rounded.

## Strings

Strings must always be encapsulated with a single `'` or double `"` quote. This serves mainly a comfort purpose when having many quotes inside your string not having to escape them all.

Escaping of special characters works just the usual way. 

```ctn
:say: 'Hello it\'s me!'
```

Beloved or Hated emojis will also work just fine. 

```ctn
:snails: '🐌🐌🐌'
```

Repeated spaces and tabs are always collapsed to a single space, and this also applies inside a string. This keeps container files tidy regardless of how they're indented, but it means you can't rely on whitespace to format text you store, e.g. a multi-line example embedded in a description.

```ctn
:example: "a  b" # becomes "a b"
```

If you need to preserve formatting exactly as written, use a heredoc block instead. This is an explicit opt-out of the whitespace collapsing described above.

```ctn
:example: <<<EOT
    {
        "foo": "bar"
    }
EOT
```

A heredoc starts with `<<<` followed by a tag name, and ends with a line containing only the matching tag. The tag can be any combination of letters, digits and underscores, and everything in between is kept exactly as written, including indentation and repeated spaces. Unlike PHP's heredoc syntax there is no automatic indentation stripping, the closing tag must be flush against the start of its line.

## Booleans and Null

There is not much to say about them:

```ctn
:nothing: null
```

```ctn
:positive: true
:negative: false
```

## Arrays

It's important to notice that all arrays are internally associative. When defining a simple list the associative key is automatically generated and represents the index of the item.

This means that the array `{'A', 'B'}` equals `{0: 'A', 1: 'B'}`.

Arrays can be defined multidimensional:

```ctn
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

::: tip
These values are used everywhere: as [parameters](/container-files/parameters), as [constructor and method arguments](/container-files/services), and inside [metadata](/container-files/metadata).
:::
