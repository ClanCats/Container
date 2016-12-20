<?php 
namespace ClanCats\Container;

use ClanCats\Container\{
	ServiceLoaderService as Service
};

class Container 
{
	/**
	 * The services
	 * 
	 * @var array
	 */
	protected $services = [];

	/**
	 * The parameters
	 * 
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * Construct a new service loader with a given cache directory
	 * 
	 * @param string 				$cacheDirectory
	 * @return void
	 */
	public function __construct(array $initalParameters = [])
	{
		$this->parameters = $initalParameters;
	}

	/**
	 * Does the container have the given parameters
	 * 
	 * @param string 			$name
	 * @return bool
	 */
	public function hasParameter(string $name) : bool
	{
		return array_key_exists($name, $this->parameters);
	}

	/**
	 * Get the given parameter with default
	 * 
	 * @param string 			$name
	 * @return bool
	 */
	public function getParameter(string $name, $default = null)
	{
		return 
	}

}