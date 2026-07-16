# Container Files

Container files (`.ctn`) are written in a very simple meta-language for declaring your **services**, their **dependencies**, and your application **parameters** in one place — separate from your PHP code.

A namespace parses one or more of these files, and the container builder compiles them down to a plain generated PHP class. That means the readable, declarative syntax you write here has **near-zero runtime overhead** once compiled.

## A first taste

```ctn
# parameters describe configuration values
:database.hostname: 'production.db.example.com'
:database.port: 7878

# services describe the objects your app is built from
@database: Acme\Database(:database.hostname, :database.port)

@repository.users: Acme\UserRepository(@database)
  - setCacheTtl(3600)
```

The example above declares two parameters, a `@database` service constructed from them, and a `@repository.users` service that depends on `@database` and receives a method call after construction. That is the essence of the language — everything else is detail.

## The building blocks

| Topic | What it covers |
|---|---|
| [Values & Types](/container-files/values) | Strings, numbers, booleans, null and arrays — the scalar values you assign. |
| [Parameters](/container-files/parameters) | Defining configuration values with `:name:` and referencing them. |
| [Services](/container-files/services) | Defining services with `@name:`, constructor arguments, references, method calls, updates and aliases. |
| [Metadata & Tags](/container-files/metadata) | Tagging services with `= key:` for tag-based lookups. |
| [Generics](/container-files/generics) | Reusable service templates parameterized by type, materialized into concrete services. |
| [Imports & Overriding](/container-files/imports) | Composing files with `import` and replacing definitions with `override`. |

Once you know the syntax, see [Parsing](/container-files/parsing) for how to load `.ctn` files from PHP, and [Composer Integration](/container-files/composer-integration) for importing definitions shipped by packages.

::: tip Syntax highlighting
Container files have no dedicated highlighter grammar, so the code samples in this documentation are highlighted as YAML — the closest match for the `key: value` and `#` comment style.
:::
