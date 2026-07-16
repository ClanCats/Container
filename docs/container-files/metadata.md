# Metadata & Tags

Metadata can be assigned to any [service definition](/container-files/services). It lets you attach arbitrary tags to services and then fetch every service matching a given metadata key at runtime — ideal for wiring up routes, event listeners, console commands and similar collections without listing them all by hand.

A metadata entry is prefixed with a `=` character.

```ctn
@controller.auth.sign_in: Controller\Auth\SignInController(@auth)
  = route: {'GET', 'POST'}, '/signin'
```

## Multiple entries per key

The metadata key is always a vector / array, so you can add multiple entries of the same type:

```ctn
@controller.auth.sign_in: Controller\Auth\SignInController(@auth)
  = route: {'GET', 'POST'}, '/signin'
  = tag: 'users'
  = tag: 'auth'
```

## Named sub-keys

The elements inside a metadata definition can have named keys:

```ctn
@app.bootstrap: Bootstrap()
  = on: 'app.start' call: 'onAppStart'
```

## Looking up tagged services

At runtime the container can return all services carrying a given metadata key, which is how you collect, for example, every controller that declared a `route`. See [Looking up services by metadata](/usage/services#looking-up-services-by-metadata-tags) for the runtime lookup API.
