# Values & Types

Wherever a container file expects a value — a [parameter](/container-files/parameters) you set, an [argument](/container-files/services) you pass to a service, an entry inside [metadata](/container-files/metadata) — it's one of a small, familiar set of types. They're the same scalars and arrays you'd write in plain PHP, so there's very little new to learn here; this page is mostly a quick reference for the exact syntax.

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

You may write integers, floats and doubles, and you don't need to declare which is which. Container files make no distinction between number types — that would only add overhead — so the value is forwarded straight to PHP, which interprets it just as it would in your own code:

```ctn
:retries: 42                       # int
:timeout: 42.01                    # float
:ratio: -42.12345678912345         # double
```

Because PHP does the interpreting, floating-point precision is PHP's too — a very large or very precise double may be stored rounded, exactly as it would be in plain PHP.

## Strings

Strings must always be wrapped in single `'` or double `"` quotes. Having both available is mostly a convenience: pick whichever quote your text doesn't contain, and you won't have to escape it. When you do need to escape a character, it works just as you'd expect:

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
