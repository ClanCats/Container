# Dynamic / Basic Container Implementation

Sometimes you don't need compilation at all — a quick script, a test, or a container whose services you assemble at runtime from data you don't have until the program is running. For those cases you may skip the factory, the builder and the cache directory entirely and just create the base container:

```php
use ClanCats\Container\Container;

$container = new Container();
```

That's the whole setup. From here you [bind services](/advanced/service-binding) and [set parameters](/usage/parameters) directly on the instance, and everything is resolved on the fly.

This is the simplest and most flexible implementation. The trade-off is that a plain container **cannot be compiled**, so it does a little more work at runtime than a generated container would. In return it has almost no limitations: you are free to bind services and parameters that are only known once your application is already running. If you're unsure whether this is the right choice for you, see [Choosing an Implementation](/usage/implementations/choosing).
