<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\ServiceLoaderService as Service;

class ServiceLoaderServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAndClassName()
    {
        $service = new Service('My\\Class\\Name');

        $this->assertEquals('My\\Class\\Name', $service->getClassName());
    }

    public function dummyServiceProvider()
    {
        return [[new Service('My\\Class\\Name')]];
    }

    /**
     * @dataProvider dummyServiceProvider
     */
    public function testShared($service)
    {
        // check default is true
        $this->assertTrue($service->isShared());

        // change it
        $service->setShared(false);
        $this->assertFalse($service->isShared());
    }
}
