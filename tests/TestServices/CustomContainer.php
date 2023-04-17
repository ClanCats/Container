<?php
namespace ClanCats\Container\Tests\TestServices;

use ClanCats\Container\{
    Container
};

class CustomContainer extends Container 
{
    protected array $resolverMethods = [
        'engine' => 'resolveEngine',
        'car' => 'resolveCar',
    ];

    protected array $serviceResolverType = [
        'engine' => 0, 'car' => 0, 'broken' => 42,
    ];

    public function __construct(array $initalParameters = [])
    {
        parent::__construct();
    }

    protected function resolveEngine() : Engine
    {
        return $this->resolvedSharedServices['engine'] = new Engine();
    }

    protected function resolveCar() : Car
    {
        return new Car($this->get('engine'));
    }
}   
