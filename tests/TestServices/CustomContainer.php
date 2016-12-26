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
		'engine' => 0, 'car' => 0
	];

	public function __construct(array $initalParameters = [])
	{
		parent::__construct();
		$this->addServiceResolverType('broken', 42);
	}

	protected function resolveEngine()
	{
		return $this->resolvedSharedServices['engine'] = new Engine();
	}

	protected function resolveCar()
	{
		return $this->resolvedSharedServices['car'] = new Car($this->get('engine'));
	}
}	