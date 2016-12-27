<?php 
namespace ClanCats\Container;

class ServiceFactoryArguments 
{
	/**
	 * Available service factory argument types
	 * Subclasses would be cleaner, but this could have 
	 * a real performance impact so lets do it oldschool 
	 */ 
	const SCALAR = 0;
	const PARAMETER = 1;
	const DEPENDENCY = 2;

	/**
	 * An array of arguments
	 * 
	 * @var array[[string, int]]
	 */
	protected $currentArguments = [];

	//public function add
}	