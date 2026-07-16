# Generics

Generics let you define a **service template** parameterized by one or more type parameters, then **materialize** it into concrete services by supplying type arguments. This removes the boilerplate of writing near-identical service definitions that differ only by a class name, a scalar, or a dependency — repositories per model, cache drivers per backend, command handlers, and so on.

## Defining a template

A generic definition looks like a normal [service](/container-files/services), except the name is followed by one or more type parameters in angle brackets. The type parameters can then be used anywhere in the body — in the class name, constructor arguments, method calls and metadata.

```ctn
@repository<T>: App\Repository(@db)
  - setModel(T::class)
  = tag: 'repository'
```

A template is **not** a service. It is registered as a generic and can never be resolved directly — only its materializations become services.

Type parameters can be used in the class name itself, and a template may consist of nothing but a constructor:

```ctn
@handler<TCommand, THandler>: THandler(@bus)
  = command: TCommand::class

@simple<T>: T
```

## Materializing

To create a concrete service, point a new service name at the template and pass the type arguments in angle brackets:

```ctn
@repository.user: @repository<App\Model\User>
@repository.post: @repository<App\Model\Post>
```

Each materialization is expanded independently, so the two services above get their own class name, arguments and method calls with the type parameter substituted throughout. A materialized service behaves like any other — it can be [updated](/container-files/services#service-updates) afterwards:

```ctn
@engine.custom: @engine.powered<100>

@engine.custom
  - setName('custom')
```

## Type arguments

A type argument can be any of the following, and it is substituted verbatim wherever the corresponding type parameter appears:

- **Class name** — resolves to the fully-qualified class-name string where used as `T::class`, or becomes the class name itself when used bare.
- **Integer, string, boolean or null** — any [scalar value](/container-files/values), substituted as a raw value.
- **Service reference** (`@name`) — kept as a dependency.
- **Parameter reference** (`:name`) — kept as a parameter reference.

```ctn
@repository.user: @repository<App\Model\User>              # class name
@cache.session:   @cache<App\Cache\RedisDriver, 3600>       # + integer
@flag.one:        @flagged<'hello', true, null>             # string, bool, null
@wrapper.logged:  @wrapper<@logger>                         # service reference
@cache.page:      @cache<App\Cache\ApcuDriver, :cache_ttl>  # parameter reference
```

Whitespace and newlines inside the angle brackets are allowed and ignored — `@pair<T, U>` can just as well be materialized as `@pair< App\First , App\Second >`.

The same type parameter may be used any number of times in the body, and arrays written in the template are preserved through expansion:

```ctn
@twin<T>: App\Twin(T::class)
  - first(T::class)
  - second(T::class)

@twin.user: @twin<App\User>
```

## Overriding

A generic definition or a materialized service can be replaced with the [`override`](/container-files/imports#overriding) keyword, exactly like a normal service:

```ctn
@repository<T>: App\Repository(@db)

override @repository<T>: App\ReadOnlyRepository(@db)

@repository.user: @repository<App\Model\User>   # uses App\ReadOnlyRepository
```

```ctn
@repository.user: @repository<App\Model\User>
override @repository.user: @repository<App\Model\Admin>
```

## Rules & errors

- **Arity must match.** Materializing with the wrong number of type arguments is an error (`@pair<A, B>` materialized with one argument throws).
- **No duplicate type parameters.** `@pair<T, T>` is rejected.
- **A template needs at least one type parameter.** `@repository<>` is a parse error.
- **Redefining needs `override`.** Declaring the same generic name twice without `override` throws.
- **Unknown templates throw.** Materializing from a name that was never defined as a generic fails.

## Generics across imports

A template can be defined in one file and materialized in another that [imports](/container-files/imports) it:

```ctn
// templates.ctn
@engine.powered<TPower>: ClanCats\Container\Tests\TestServices\Engine
  - setPower(TPower)
@named.producer<TName>: ClanCats\Container\Tests\TestServices\Producer(TName)
```

```ctn
// app.ctn
import templates

@engine.weak: @engine.powered<50>
@engine.strong: @engine.powered<9000>
@producer.acme: @named.producer<'ACME Corp'>
```

## Compilation

Generics are purely a container-file authoring convenience. They are fully expanded into ordinary service definitions during interpretation, so the **compiled container contains no generic artifacts** — only the concrete, materialized services. There is zero generics-related overhead at runtime.
