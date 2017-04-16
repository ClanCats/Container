<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ContainerNamespace
};

class ContainerNamespaceTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
        $namespace = new ContainerNamespace();
        $this->assertInstanceOf(ContainerNamespace::class, $namespace);
    }
}
