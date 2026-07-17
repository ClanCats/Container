# Overview

CC Container is a zero-dependency PHP IoC / service container (PHP ≥ 7.4). Its distinguishing feature is a small meta-language — **container files** (`.ctn`) — for declaring your services, parameters and configuration, plus the ability to **compile** those definitions into a plain generated PHP class for near-zero runtime overhead.

If the words _service_, _container_ or _dependency injection_ don't mean much to you yet, start with [Core Concepts](/introduction/core-concepts). If you just want to get something running, jump to [Getting Started](/introduction/getting-started).

## Two ways to define services

There are two ways to describe your services, and they build the exact same container. You can mix them, but most projects lean on one.

| | Container files (`.ctn`) | Programmatic API (PHP) |
| --- | --- | --- |
| **How** | Declare services in a `.ctn` file | Call `bind()` / build a `ServiceDefinition` in PHP |
| **Compilable** | Yes — compiles to a fast generated class | Only when fed through the builder |
| **Best for** | The bulk of your application's service graph | Dynamic services decided at runtime, or when you don't want a meta-language |
| **Start here** | [Container Files](/container-files/introduction) | [Programmatic API](/advanced/service-binding) |

The recommended path for a typical application is: declare your services in `.ctn` files and let the [Default implementation](/usage/implementations/default) compile them. Reach for the programmatic API when you need to register or override a service on a *live* container.

Whichever path you choose, the **runtime API is identical** — see [Using the Container](/usage/container).

## How compilation works

The whole point of this library is that the expensive work (parsing files, resolving the dependency graph) happens **once, at build time**, and produces a plain PHP class. At runtime you just include that class. See [The Container Lifecycle](/usage/lifecycle) for the full pipeline.

## Glossary

These terms are used consistently throughout the documentation:

 * **Service** — a PHP object managed by the container (e.g. a logger, a database connection). See [Core Concepts](/introduction/core-concepts).
 * **Container file (`.ctn`)** — a file written in the meta-language that declares services and parameters.
 * **Parameter** — a scalar/array configuration value, referenced with a leading `:`.
 * **Shared service** — resolved once and cached; every `get()` returns the same instance (the default).
 * **Factory service** — rebuilt on every `get()`, returning a fresh instance. (Not to be confused with the `ServiceFactory` *class*.)
 * **Binding** — registering a service on a *live* container at runtime via `bind()`. See [Service Binding](/advanced/service-binding).
 * **Compiling / building** — turning declarations into a generated PHP container class via the `ContainerBuilder`.
