# Container Files

Sooner or later, wiring your objects together by hand in PHP starts to hurt. A repository needs a database, the database needs a host and a port, the mailer needs a transport, and before long you have a tangle of `new` calls threading dependencies through one another — repeated in every entry point, and rebuilt from scratch on every single request.

Container files (`.ctn`) are the answer to that tangle. You declare your **services**, their **dependencies**, and your application **parameters** once, in one readable place, in a small meta-language kept separate from your PHP code. A [namespace](/container-files/parsing) parses those files and the container builder compiles them down to a plain generated PHP class — so the friendly, declarative syntax you write here carries **near-zero runtime overhead** once compiled.

## A first taste

Here is a small container file that sets up a database and a repository that depends on it:

```ctn
# parameters describe configuration values
:database.hostname: 'production.db.example.com'
:database.port: 7878

# services describe the objects your application is built from
@database: Acme\Database(:database.hostname, :database.port)

@repository.users: Acme\UserRepository(@database)
  - setCacheTtl(3600)
```

That is the whole language in miniature. We declared two parameters, a `@database` service constructed from them, and a `@repository.users` service that receives `@database` as a constructor argument and a `setCacheTtl` method call after it is built. Ask the container for `repository.users` and it assembles the entire graph for you — no `new` calls, no wiring by hand.

If the syntax looks unfamiliar, don't worry. Every line here maps directly onto ordinary PHP object construction, and each symbol has a job you'll pick up in a page or two: `:` marks a parameter, `@` marks a service, `-` marks a method call. That's most of it.

## The building blocks

Each page below takes one part of the language and covers it on its own. They're ordered so each builds on the last, so reading top to bottom is the gentlest path — but feel free to jump straight to what you need.

| Topic | What it covers |
|---|---|
| [Values & Types](/container-files/values) | Strings, numbers, booleans, null and arrays — the values you assign and pass around. |
| [Parameters](/container-files/parameters) | Pulling configuration out into named values with `:name:`, and referencing them. |
| [Services](/container-files/services) | Declaring services with `@name:`, constructor arguments, references, method calls, updates and aliases. |
| [Metadata & Tags](/container-files/metadata) | Tagging services with `= key:` so you can collect them by tag at runtime. |
| [Generics](/container-files/generics) | Reusable service templates parameterized by type, materialized into concrete services. |
| [Imports & Overriding](/container-files/imports) | Composing many files with `import` and replacing definitions with `override`. |

Once the syntax feels familiar, see [Parsing](/container-files/parsing) for how to load `.ctn` files from PHP, and [Composer Integration](/container-files/composer-integration) for importing definitions shipped by packages.

::: tip Prefer to learn by doing?
[Getting Started](/introduction/getting-started) builds a small working container from scratch, one line at a time. This section is the reference you'll return to once you know your way around.
:::

::: tip Syntax highlighting
Container files have no dedicated highlighter grammar built into this site, so the code samples here are highlighted as YAML — the closest match for the `key: value` and `#` comment style. Your editor can do better: a `tmLanguage` grammar is available for real `.ctn` syntax highlighting.
:::
