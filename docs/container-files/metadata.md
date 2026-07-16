# Metadata & Tags

Some collections are painful to maintain by hand: every controller that declares a route, every console command, every event listener. Listing them all in one central place means editing that list each time you add one — and forgetting to is an easy mistake.

Metadata turns that around. You attach arbitrary tags to a [service](/container-files/services) right where it's defined, then ask the container at runtime for every service carrying a given tag. The list assembles itself.

A metadata entry is prefixed with a `=` character:

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
