<?php
namespace ClanCats\Container\Tests\TestServices;

use ClanCats\Container\{
    Container
};

class CustomContainer extends Container 
{
    protected $resolverMethods = [
        'engine' => 'resolveEngine',
        'car' => 'resolveCar',
    ];

    protected $serviceResolverType = [
        'engine' => 0, 'car' => 0, 'broken' => 42,
    ];

    public function __construct(array $initalParameters = [])
    {
        parent::__construct();
    }

    protected function resolveEngine()
    {
        return $this->resolvedSharedServices['engine'] = new Engine();
    }

    protected function resolveCar()
    {
        return new Car($this->get('engine'));
    }
}   