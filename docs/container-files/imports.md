# Imports & Overriding

Container files are rarely a single file. You split definitions across many files and compose them, then selectively replace definitions when needed.

## Imports

Other container files can be imported from the container namespace by their import name.

```ctn
import config
import app/dashboard
import app/user
import app/shop
```

An import name is not a file path — it is the name the file was registered under in the namespace (for example via `importDirectory()`), so `app/user` resolves to whatever path is mapped to that name. See [Parsing](/container-files/parsing) for how those names are established, and [Composer Integration](/container-files/composer-integration) for importing files shipped by packages.

## Overriding

Services and parameters must be **explicitly** overwritten if they have already been defined. Without the `override` keyword, redefining an existing name is an error — this protects you from silently shadowing a definition, especially across imported files.

```
:ship: 'Star Destroyer'

override :ship: 'X-Wing'
```

The same applies to services:

```ctn
@producer.default: Acme\Producer('standard')

override @producer.default: Acme\Producer('massive')
```

::: tip
`override` also works with [generic definitions and materializations](/container-files/generics#overriding).
:::
