<?php 
namespace ClanCats\Container;

use ClanCats\Container\{

	// exceptions
	Exceptions\UnknownServiceException,

	// service
	ServiceLoaderService as Service,
};

class Container 
{
	/**
	 * The parameters
	 * 
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * A mapping array to know how a service must be resolved.
	 * 
	 * @var array
	 */
	const RESOLVE_METHOD = 0;
	const RESOLVE_FACTORY = 1;
	const RESOLVE_SHARED = 2;
	protected $serviceResolverType = [];

	/**
	 * An array of methods that resolve a service inside the current container.
	 * This is mostly for the cached / dumped container which creates custom methods
	 * for each service to (try to) improve performance of the container.
	 * 
	 * @var array
	 */
	protected $resolverMethods = [];

	/**
	 * An array of factory callbacks 
	 * 
	 * @var array[ServiceFactoryInterface|Closure]
	 */
	protected $resolverFactories = [];

	/**
	 * An array of factory callbacks that will be shared across the container.
	 * 
	 * @var array[ServiceFactoryInterface|Closure]
	 */
	protected $resolverFactoriesShared = [];

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
		return $this->hasParameter($name) 
	}

	/**
	 * Get a service by the given name
	 * 
	 * @param string 			$serviceName
	 * @return mixed
	 */
	public function get(string $serviceName)
	{
		if (!isset($this->serviceResolverType[$serviceName]))
		{
			throw new UnknownServiceException('Could not find service named "' . $serviceName . '" registered in the container.');
		}

		switch ($this->serviceResolverType[$serviceName]) 
		{
			case static::RESOLVE_METHOD:
				return $this->resolveServiceFromMethod($serviceName);
			break;

			case static::RESOLVE_FACTORY:
				return $this->resolveServiceFromFactory($serviceName);
			break;

			case static::RESOLVE_SHARED:
				// the shared resolver has no 
				return $this->resolveServiceFromShared($serviceName);
			break;
			
			default:
				throw new UnknownServiceException('Could not resolve service named "' . $serviceName . '", the resolver type is unkown.');
			break;
		}
	}

	/**
	 * Default resolver for all services that are defined 
	 * directly in the container.
	 * 
	 * @param name 				$name
	 */
	protected function resolveServiceFromMethod(string $name)
	{
		return $this->{$name}();
	}

	/**
	 * Resolver that will store the returned value for the next
	 * access.
	 * 
	 * @param string 			$method
	 */
	protected function resolveServiceFromShared(string $name)
	{

	}

	/**
	 * Resolver that will simply return the factoryies value 
	 * 
	 * @param string 			$method
	 */
	protected function resolveServiceFromFactory(string $name)
	{

	}
}	