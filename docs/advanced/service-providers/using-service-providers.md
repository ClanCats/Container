# What are Service Providers

A service provider is a lazy source of services. Instead of registering every single service definition up front, a provider declares *which* service names it is able to build and only constructs a given service the moment it is actually requested.

This is invaluable for large or dynamic class graphs: you may have hundreds of potential services, but a single request usually only touches a handful of them. A provider lets you defer all of that work — you only pay for what you resolve.

## The provider interface

Every provider implements `ServiceProviderInterface`, which requires two methods.

```php
interface ServiceProviderInterface
{
    // The names this provider can build.
    public function provides() : array;

    // Build one service; return [$service, $shared].
    public function resolve(string $serviceName, Container $container) : array;
}
```

`provides()` returns an array of service names. `resolve()` builds the requested service and returns a two-element array: the service instance and a boolean telling the container whether the result should be **shared** (cached as a singleton) or rebuilt on every request.

## Registering a provider

Hand a provider instance to the container with `register`. The container walks the provider's `provides()` list and routes every one of those names through the provider.

```php
$container->register(new MyServiceProvider());
```

From then on, `$container->get('someServiceTheProviderProvides')` will call the provider's `resolve()` behind the scenes — and, if the provider reported the service as shared, cache the result so the next `get` returns the same instance.

[~ PHPDoc](/src/Container.php#register)

## Looking up classes without building

Resolving a service means constructing it. Sometimes, though, you only need to know the *class name* a service would produce — for instance when doing tag / type based lookups — without paying the cost of instantiation.

A provider can opt into this by additionally implementing `ServiceProviderClassLookupInterface`:

```php
interface ServiceProviderClassLookupInterface
{
    public function lookupClassName(string $serviceName, Container $container) : string;
}
```

When present, the container uses `lookupClassName` inside `getClassForService` to answer "what class is this service?" without ever calling `resolve`.

## Which provider to use

- **[Array Service Provider](/advanced/service-providers/array-service-provider)** — the batteries-included `ServiceProviderArray`. Describe your services as a plain configuration array and you are done. Reach for this first.
- **[Custom Service Provider](/advanced/service-providers/custom-service-provider)** — implement `ServiceProviderInterface` yourself when service construction needs real logic that a static array cannot express.
