# Parameters

Parameters are configuration values — hostnames, ports, feature flags, paths — that live alongside your service definitions. A parameter is always prefixed with a `:` character.

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

Anywhere an argument is expected — a constructor argument, a method call argument — you can reference a parameter by prefixing its name with `:`. The container resolves the value at build time.

```ctn
:database.hostname: 'production.db.example.com'

@database: Acme\Database(:database.hostname)
```

See [Services](/container-files/services) for the full picture on constructor and method arguments, and [Imports & Overriding](/container-files/imports) for replacing a parameter that was already defined.
