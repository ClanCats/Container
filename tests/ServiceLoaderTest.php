<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    ServiceLoader,
    ServiceLoaderService as Service
};

class ServiceLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testBindingSingleService()
    {
        $loader = new ServiceLoader('test');
        $loader->bindService('foo', new Service('Some\\Class'));

        $bindedServies = $loader->getBindedServices();

        $this->assertCount(1, $bindedServies);
        $this->assertInstanceOf(Service::class, $bindedServies['foo']);
        $this->assertEquals('Some\\Class', $bindedServies['foo']->getClassName());
    }
}
