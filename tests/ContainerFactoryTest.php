<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container,
    ContainerFactory,
    ContainerBuilder
};
use ClanCats\Container\Tests\TestServices\{
    Engine
};

class ContainerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The temporary cache directory used by the current test
     *
     * @var string
     */
    private $cacheDir;

    protected function setUp() : void
    {
        $this->cacheDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ccc_factory_' . uniqid();
        mkdir($this->cacheDir);
    }

    protected function tearDown() : void
    {
        if (is_dir($this->cacheDir)) {
            foreach (glob($this->cacheDir . DIRECTORY_SEPARATOR . '*') as $file) {
                @unlink($file);
            }
            @rmdir($this->cacheDir);
        }
    }

    public function testCacheDirectoryNormalization()
    {
        $factory = new ContainerFactory('/tmp/foo');
        $this->assertEquals('/tmp/foo' . DIRECTORY_SEPARATOR, $factory->getCacheDirectory());

        // an already terminated path stays untouched
        $factory = new ContainerFactory('/tmp/foo' . DIRECTORY_SEPARATOR);
        $this->assertEquals('/tmp/foo' . DIRECTORY_SEPARATOR, $factory->getCacheDirectory());

        // setter also normalizes
        $factory->setCacheDirecotry('/tmp/bar');
        $this->assertEquals('/tmp/bar' . DIRECTORY_SEPARATOR, $factory->getCacheDirectory());
    }

    public function testDebugMode()
    {
        $this->assertFalse((new ContainerFactory('/tmp'))->isDebugMode());
        $this->assertTrue((new ContainerFactory('/tmp', true))->isDebugMode());
    }

    public function testCreateGeneratesAndResolves()
    {
        $factory = new ContainerFactory($this->cacheDir);

        $container = $factory->create('PHPUnitFactoryCold', function(ContainerBuilder $builder) {
            $builder->add('engine', Engine::class);
        });

        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(Engine::class, $container->get('engine'));

        // the cache file has been written to disk
        $this->assertFileExists($this->cacheDir . DIRECTORY_SEPARATOR . 'PHPUnitFactoryCold.php');
    }

    public function testInitialParametersArePassed()
    {
        $factory = new ContainerFactory($this->cacheDir);

        $container = $factory->create('PHPUnitFactoryParams', function(ContainerBuilder $builder) {
            // no services required
        }, ['env' => 'testing']);

        $this->assertEquals('testing', $container->getParameter('env'));
    }

    public function testExistingClassSkipsBuilder()
    {
        $factory = new ContainerFactory($this->cacheDir);

        $builderRuns = 0;
        $builderCallback = function(ContainerBuilder $builder) use (&$builderRuns) {
            $builderRuns++;
            $builder->add('engine', Engine::class);
        };

        // first call generates the class and runs the builder
        $factory->create('PHPUnitFactoryExisting', $builderCallback);
        $this->assertEquals(1, $builderRuns);

        // second call finds the class already loaded and returns early,
        // the builder callback must not be invoked again
        $container = $factory->create('PHPUnitFactoryExisting', function(ContainerBuilder $builder) use (&$builderRuns) {
            $builderRuns++;
        });

        $this->assertEquals(1, $builderRuns);
        $this->assertInstanceOf(Container::class, $container);
    }

    public function testCachedFileIsUsedWhenNotInDebugMode()
    {
        // pre-seed a stale cache file that does NOT define the container class
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'PHPUnitFactoryStaleNonDebug.php';
        file_put_contents($cacheFile, "<?php\n// stale cache without a class definition\n");

        $factory = new ContainerFactory($this->cacheDir);

        // not in debug mode: the existing (stale) file is used instead of regenerating,
        // so instantiating the missing class fails
        $this->expectException(\Error::class);
        $factory->create('PHPUnitFactoryStaleNonDebug', function(ContainerBuilder $builder) {
            $builder->add('engine', Engine::class);
        });
    }

    public function testDebugModeRegeneratesExistingCacheFile()
    {
        // pre-seed a stale cache file that does NOT define the container class
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'PHPUnitFactoryStaleDebug.php';
        file_put_contents($cacheFile, "<?php\n// stale cache without a class definition\n");

        $factory = new ContainerFactory($this->cacheDir, true);

        // debug mode regenerates the file even though it already exists,
        // so the container is built correctly from the fresh definition
        $container = $factory->create('PHPUnitFactoryStaleDebug', function(ContainerBuilder $builder) {
            $builder->add('engine', Engine::class);
        });

        $this->assertInstanceOf(Container::class, $container);
        $this->assertInstanceOf(Engine::class, $container->get('engine'));
    }

    public function testNonWritableDirectoryThrows()
    {
        $factory = new ContainerFactory('/this/does/not/exist/at/all');

        $this->expectException(\ClanCats\Container\Exceptions\ContainerException::class);
        $factory->create('PHPUnitFactoryNoDir', function(ContainerBuilder $builder) {
            // never reached, the directory is not writable
        });
    }

    public function testNonWritableCacheFileThrows()
    {
        $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . 'PHPUnitFactoryReadonlyFile.php';
        file_put_contents($cacheFile, "<?php\n");
        chmod($cacheFile, 0444);

        // when running as root the read only flag is ignored, skip in that case
        if (is_writable($cacheFile)) {
            chmod($cacheFile, 0644);
            $this->markTestSkipped('Cannot make the cache file read only (running as root?).');
        }

        // debug mode forces regeneration so the writability of the existing file is checked
        $factory = new ContainerFactory($this->cacheDir, true);

        try {
            $this->expectException(\ClanCats\Container\Exceptions\ContainerException::class);
            $factory->create('PHPUnitFactoryReadonlyFile', function(ContainerBuilder $builder) {
                // never reached, the target file is not writable
            });
        } finally {
            // restore write permission so the file can be cleaned up
            chmod($cacheFile, 0644);
        }
    }
}
