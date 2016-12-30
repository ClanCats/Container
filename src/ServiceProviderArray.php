<?php 
namespace ClanCats\Container;

use ClanCats\Container\{
	Exceptions\UnknownServiceException,
	Exceptions\InvalidServiceException
};


class ServiceProviderArray implements ServiceProviderInterface 
{
	/**
	 * Array of to be provided services 
	 * You can subclass the ServiceProviderArray and simply overwrite 
	 * this attribute. You can also create an instance of the array provider and 
	 * set your services using the `setServices() method.
	 * 
	 * @var array
	 */
	protected $services = [];

	/**
	 * Set the providers services 
	 * 
	 * @param array 			$services
	 */
	public function setServices(array $services) : void
	{
		$this->services = $services;
	}

	/**
	 * What services are provided by the service provider
	 * 
	 * @return array[string]
	 */
	public function provides() : array
	{
		return array_keys($this->services);
	}

	/**
	 * Resolve a service with the given name 
	 * 
	 * @param string 					$serviceName
	 * @param Container 				$container
	 * @return array[mixed, bool]
	 */
	public function resolve(string $serviceName, Container $container) : array
	{
		if (!isset($this->services[$serviceName]))
		{
			throw new UnknownServiceException('The service provider "' . get_class($this) . '" does not support service resolving of the service "' . $serviceName . '"');
		}

		$serviceConfiguration = $this->services[$serviceName];

		if (!isset($serviceConfiguration['class']))
		{
			throw new InvalidServiceException('The "' . $serviceName . '" service configuration must define a "class" attribute.');
		}

		// construct the service factory
		$factory = ServiceFactory::for($serviceConfiguration['class'], $serviceConfiguration['arguments'] ?? []);

		// add service method calls if configured
		if (isset($serviceConfiguration['calls']))
		{
			foreach($serviceConfiguration['calls'] as $call)
			{
				if (isset($call['method']) && isset($call['arguments']))
				{
					$factory->calls($call['method'], $call['arguments']);
				}
				else 
				{
					throw new InvalidServiceException('Every "' . $serviceName . '" service call must have the attributes "method" and "arguments".');
				}
			}
		}

		// resolve the service 
		$service = $factory->create($container);

		// if the service is not shared skip the store
		if ((!isset($serviceConfiguration['shared'])) || $serviceConfiguration['shared'] !== false)
		{
			return [$service, true];
		}

		return [$service, false];
	}
}	