# Choosing an Implementation

How you construct your container is up to you, but you should decide up front whether you want to **compile** the dependency graph or not. All three implementations produce a container with the exact same [runtime API](/usage/container) — they differ only in how the container is assembled.

| | [Default](/usage/implementations/default) | [Dynamic](/usage/implementations/dynamic) | [Custom Build](/usage/implementations/custom-build) |
| --- | --- | --- | --- |
| **How services are defined** | `.ctn` container files | PHP `bind()` calls at runtime | Any source (JSON, array, …) fed to the builder |
| **Compiled to a PHP class** | ✅ Yes | ❌ No | ✅ Yes |
| **Runtime speed** | Fastest | Slower | Fastest |
| **Needs a writable cache dir** | ✅ | ❌ | ✅ |
| **Flexibility at runtime** | Add/override via `bind()` | Unlimited | Add/override via `bind()` |
| **Best for** | Most applications | Tests, tiny scripts, fully dynamic graphs | You want compilation but not the `.ctn` language |

## Recommendation

* Use the **[Default](/usage/implementations/default)** implementation for real applications — declare your services in `.ctn` files and let them compile.
* Use the **[Dynamic](/usage/implementations/dynamic)** container for tests, throwaway scripts, or when the service graph is only known at runtime.
* Use a **[Custom Build](/usage/implementations/custom-build)** when you want the compilation benefit but prefer to describe services in your own format instead of `.ctn` files.

You can always mix in runtime [service binding](/advanced/service-binding) on top of a compiled container.

See [The Container Lifecycle](/usage/lifecycle) for what "compiling" actually does.
