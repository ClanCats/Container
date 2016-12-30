<?php
namespace ClanCats\Container\Tests\TestServices;

use ClanCats\Container\{
	ServiceProviderArray
};

class CustomServiceProviderArray extends ServiceProviderArray 
{
	protected $services = 
	[
		'car' => 
		[
			'class' => Car::class,
			'arguments' => ['@engine', '@producer'],
		],

		'engine' => 
		[
			'class' => Engine::class,
			'calls' => [
				['method' => 'setPower', 'arguments' => [315]]
			]
		],

		'producer' => 
		[
			'class' => Producer::class,
			'arguments' => ['Audi'],
		]
	];
}	