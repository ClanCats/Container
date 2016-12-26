<?php
namespace ClanCats\Container\Tests;

use ClanCats\Container\{
    Container
};
use ClanCats\Container\Tests\TestServices\{
    Car, CarFactory, Engine
};

class ServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testBindServiceFactoryClass()
    {
        $container = new Container();

        $container->bindSharedFactory('d8', function($c) 
        {
            $engine = new Engine(); $engine->ps = 300; return $engine;
        });

        $container->bindSharedFactory('t8', function($c) 
        {
            $engine = new Engine(); $engine->ps = 325; return $engine; 
        });

        $container->bind('diesel', new CarFactory('d8'), false);
        $container->bind('benzin', new CarFactory('t8'), false);
        $container->bind('demo', new CarFactory('t8'), true);

        $this->assertEquals(300, $container->get('diesel')->engine->ps);
    } 
}
