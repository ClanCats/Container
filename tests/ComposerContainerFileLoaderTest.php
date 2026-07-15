<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\ComposerContainerFileLoader;

use Composer\Composer;
use Composer\Config;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Composer\Repository\RepositoryManager;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\CompletePackage;
use Composer\Package\Package;

class ComposerContainerFileLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The temporary vendor directory the map file is written to
     *
     * @var string
     */
    private $vendorDir;

    protected function setUp() : void
    {
        $this->vendorDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ccc_vendor_' . uniqid();
        mkdir($this->vendorDir);
    }

    protected function tearDown() : void
    {
        if (is_dir($this->vendorDir)) {
            foreach (glob($this->vendorDir . DIRECTORY_SEPARATOR . '*') as $file) {
                @unlink($file);
            }
            @rmdir($this->vendorDir);
        }
    }

    /**
     * Build a mocked composer package
     *
     * @param string $className The package mock class (CompletePackage or Package)
     * @param string $name
     * @param array<string, string> $extra
     */
    private function packageMock(string $className, string $name, array $extra)
    {
        $package = $this->createMock($className);
        $package->method('getName')->willReturn($name);
        $package->method('getExtra')->willReturn($extra);

        return $package;
    }

    /**
     * Build a mocked composer event returning the given packages and vendor dir
     *
     * @param array<mixed> $packages
     */
    private function eventMock(array $packages) : Event
    {
        $localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $localRepository->method('getCanonicalPackages')->willReturn($packages);

        $repositoryManager = $this->createMock(RepositoryManager::class);
        $repositoryManager->method('getLocalRepository')->willReturn($localRepository);

        $config = $this->createMock(Config::class);
        $config->method('get')->willReturn($this->vendorDir);

        $composer = $this->createMock(Composer::class);
        $composer->method('getRepositoryManager')->willReturn($repositoryManager);
        $composer->method('getConfig')->willReturn($config);

        $event = $this->createMock(Event::class);
        $event->method('getComposer')->willReturn($composer);
        $event->method('getIO')->willReturn($this->createMock(IOInterface::class));

        return $event;
    }

    public function testGenerateMap()
    {
        $package = $this->packageMock(CompletePackage::class, 'acme/widgets', [
            'container' => [
                '@main' => 'widgets.ctn',
                'admin' => 'admin.ctn',
            ],
        ]);

        ComposerContainerFileLoader::generateMap($this->eventMock([$package]));

        $mapFile = $this->vendorDir . '/container_map.php';
        $this->assertFileExists($mapFile);

        $map = require $mapFile;
        $this->assertIsArray($map);

        // the "@main" key maps to the bare package name
        $this->assertArrayHasKey('acme/widgets', $map);

        // every other key is prefixed with the package name
        $this->assertArrayHasKey('acme/widgets/admin', $map);

        // the container file itself is prefixed with the package name and the
        // "__DIR__" placeholder has been rewritten to the resolved vendor directory
        $this->assertStringStartsWith(realpath($this->vendorDir), $map['acme/widgets']);
        $this->assertStringEndsWith('acme/widgets/widgets.ctn', $map['acme/widgets']);
        $this->assertStringEndsWith('acme/widgets/admin.ctn', $map['acme/widgets/admin']);
    }

    public function testGenerateMapSkipsPackagesWithoutContainer()
    {
        // a complete package without a "container" extra
        $withoutContainer = $this->packageMock(CompletePackage::class, 'acme/plain', [
            'other' => ['stuff'],
        ]);

        // a complete package with an empty "container" extra
        $emptyContainer = $this->packageMock(CompletePackage::class, 'acme/empty', [
            'container' => [],
        ]);

        // a package that is not a CompletePackage instance is ignored entirely
        $nonComplete = $this->packageMock(Package::class, 'acme/incomplete', [
            'container' => ['@main' => 'nope.ctn'],
        ]);

        ComposerContainerFileLoader::generateMap($this->eventMock([
            $withoutContainer, $emptyContainer, $nonComplete,
        ]));

        $map = require $this->vendorDir . '/container_map.php';

        $this->assertIsArray($map);
        $this->assertEmpty($map);
    }
}
