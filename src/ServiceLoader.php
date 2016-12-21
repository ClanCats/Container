<?php 
namespace ClanCats\Container;

use ClanCats\Container\{
	ServiceLoaderService as Service
};

class ServiceLoader 
{
	/**
	 * Service loader file cache
	 * 
	 * @var string
	 */
	protected $cacheDirectory = null;

	/**
	 * An array of binded services
	 * 
	 * @param array[string => Service]
	 */
	protected $bindedServices = [];

	/**
	 * Construct a new service loader with a given cache directory
	 * 
	 * @param string 				$cacheDirectory
	 * @return void
	 */
	public function __construct(string $cacheDirectory)
	{
		$this->cacheDirectory = $cacheDirectory;
	}

	/**
	 * Adds the given service to the loader
	 * 
	 * @param string 			$name
	 * @param Service 			$service
	 * @return void
	 */
	public function bindService(string $name, Service $service) : void
	{
		$this->bindedServices[$name] = $service;
	}

	/**
	 * Returns an array of the currently binded services
	 * 
	 * @return array[string => Service]
	 */
	public function getBindedServices() : array
	{
		return $this->bindedServices;
	}
}