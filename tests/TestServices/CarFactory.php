<?php
namespace ClanCats\Container\Tests\TestServices;

use ClanCats\Container\{
    Container, ServiceFactoryInterface
};

class CarFactory implements ServiceFactoryInterface 
{
    protected $engineName;

    public function __construct(string $engineName)
    {
        $this->engineName = $engineName;
    }

    public function create(Container $container)
    {
        return new Car($container->get($this->engineName));
    }
}   