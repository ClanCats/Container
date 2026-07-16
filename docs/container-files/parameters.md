# Parameters

Hardcoding a hostname or a timeout directly into a service definition works — until the same value is needed in two places, or has to change between environments, and you find yourself hunting it down across your files. Parameters give those configuration values a name of their own. You define hostnames, ports, feature flags and paths once, then reference them wherever they're needed.

A parameter is always prefixed with a `:` character:

```ctn
:database.hostname: "production.db.example.com"
:database.port: 7878
:database.cache: true
```

The value can be any of the supported [value types](/container-files/values), including arrays:

```ctn
:cors.allowed_origins: {'https://example.com', 'https://app.example.com'}
```

## Referencing parameters

Anywhere an argument is expected — a constructor argument, a method call argument — you may reference a parameter by prefixing its name with `:`. The value is resolved at build time and baked into the compiled container, so there's no lookup cost at runtime.

```ctn
:database.hostname: 'production.db.example.com'
:database.port: 7878

@database: Acme\Database(:database.hostname, :database.port)
```

Now the database's configuration lives in named values you can read at a glance and change in one place. See [Services](/container-files/services) for the full picture on constructor and method arguments, and [Imports & Overriding](/container-files/imports) for keeping configuration in its own file and replacing a parameter that was already defined.
