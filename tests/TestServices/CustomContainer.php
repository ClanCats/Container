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

	public function __construct(array $initalParameters = [])
	{
		parent::__construct();
		$this->addServiceResolverType('broken', 42);
	}

	protected function resolveEngine()
	{
		//return 
	}
}	