# Dynamic / Basic Container 

Just create an instance of the base container and you are good to go.

```php
use ClanCats\Container\Container;

$container = Container;
```

This is the simplest and the most dynamic implementation. But keep in mind this type of container **cannot be compiled**, which makes it a little bit slower. But it therefor has almost no limitations when it comes to service binding and your parameters.
