# Custom Namespace

By default a [`ContainerNamespace`](/container-files/parsing) reads container files straight from the local filesystem. If you need to load `.ctn` source from somewhere else — a cache, a database, a ZIP archive, or files that need decrypting or preprocessing first — you extend `ContainerNamespace` and override how it reads code.

## The extension point

The single method responsible for turning a file path into raw `.ctn` source is `getCodeFromFile()`. It is `protected`, so a subclass can override it while reusing all the parsing, importing and storage logic.

```php
use ClanCats\Container\ContainerNamespace;

class CachedContainerNamespace extends ContainerNamespace
{
    public function __construct(private CacheInterface $cache, array $paths = [])
    {
        parent::__construct($paths);
    }

    protected function getCodeFromFile(string $containerFilePath): string
    {
        $key = 'ctn.' . md5($containerFilePath);

        if ($cached = $this->cache->get($key)) {
            return $cached;
        }

        $code = parent::getCodeFromFile($containerFilePath);
        $this->cache->set($key, $code);

        return $code;
    }
}
```

Everything else — `parse()`, `getCode()`, import resolution — flows through this method, so overriding it is enough to change the source of your container files without touching the parser.

## Custom path resolution

The import name → path map lives in the protected `$paths` property and is populated by `importDirectory()` and `importFromVendor()`. To register files from a source that isn't a directory or a Composer vendor map, add your own loader that fills `$paths`:

```php
class DatabaseContainerNamespace extends ContainerNamespace
{
    public function importFromDatabase(\PDO $pdo): void
    {
        $stmt = $pdo->query('SELECT import_name, file_path FROM container_files');

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $this->paths[$row['import_name']] = $row['file_path'];
        }
    }
}
```

Once `$paths` is populated, `import` statements inside your `.ctn` files resolve against it exactly as they would for a filesystem namespace.

::: tip
For the common case of importing container files shipped by Composer packages, you don't need a custom namespace — use the built-in [Composer integration](/container-files/composer-integration) instead.
:::
