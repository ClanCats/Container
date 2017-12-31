# Custom builded Implementation

Don't want to use container files but still want to compile / serialize the container? Then this is the implementation for you.

## Json Container

In this particular example we are going to build our container using a json document.

> Note: Read more about the [Container Builder](docs://@todo/).

### Json Service definitions 

As a first step we are going to create the json file that will define our services.

```json
{
    "session": {
        "class": "Session",
        "arguments": [ "@session.adapter", ":session.lifetime" ]
    },
    "session.adapter": {
        "class": "DatabaseSession",
        "arguments": ["@pdo"]
    }
}
```

Now we can create a new `ContainerFactory` that will load the json data and forward it to the builder.

```php
// create 
$factory = new \ClanCats\Container\ContainerFactory(__DIR__ . '/cache');

$container = $factory->create('JsonContainer', function($builder) use($services)
{
	// load the services from the json file into an array.
	$services = json_decode(trim(file_get_contents(__DIR__ . '/myservices.json')), true);

	// add the array to the container builder
    $builder->addArray($services);
});
```
