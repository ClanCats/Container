<?php 
namespace ClanCats\Container;

use ClanCats\Container\{
	ServiceLoaderService as Service
};

class ContainerBuilder 
{
	/**
	 * An array of binded services
	 * 
	 * @param array[string => Service]
	 */
	protected $bindedServices = [];

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