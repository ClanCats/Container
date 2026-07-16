---
layout: home

hero:
  name: ClanCats Container
  text: Fast, compilable PHP dependency injection.
  tagline: A PHP Service Container featuring a simple meta-language with fast and compilable dependency injection. Requires PHP >= 7.4.
  actions:
    - theme: brand
      text: Get Started
      link: /introduction/getting-started
    - theme: alt
      text: Container File Syntax
      link: /container-files/syntax
    - theme: alt
      text: View on GitHub
      link: https://github.com/ClanCats/Container

features:
  - title: Minimal overhead
    details: Very fast with no additional dependencies. In production serving millions of requests every day.
  - title: Compilable definitions
    details: A container builder lets you compile / serialize your service definitions into a plain generated PHP class for near-zero runtime overhead.
  - title: Container files
    details: A simple meta-language to define your services and manage your application config, with Composer integration to import definitions from packages.
  - title: Singleton & Factory resolvers
    details: Both shared (singleton) and factory service resolvers, plus a metadata system enabling intuitive tag-based service lookups.
  - title: Lazy service providers
    details: Lazy service providers for big and dynamic class graphs, so you only build what you actually resolve.
  - title: Explicit by design
    details: Only named services and no auto-wiring — the dependency graph stays explicit and predictable.
---
