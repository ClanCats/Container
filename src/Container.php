<?php 
namespace ClanCats\Container;

use Closure;
use ClanCats\Container\{

	// exceptions
	Exceptions\UnknownServiceException,
	Exceptions\InvalidServiceException
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
	 * Array of already resolved shared factories
	 * 
	 * @var array
	 */
	protected $resolvedSharedServices = [];

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
		return $this->hasParameter($name) ? $this->parameters[$name] : $default;
	}

	/**
	 * Set the given parameter with value
	 * 
	 * @param string 			$name
	 * @param mixed 			$value
	 * @return bool
	 */
	public function setParameter(string $name, $value)
	{
		return $this->parameters[$name] = $value;
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
			// Default resolver for all services that are defined 
	 		// directly in the container. We can skip here an unnecessary method call.
			case static::RESOLVE_METHOD:
				return $this->{$this->resolverMethods[$serviceName]}();
			break;

			case static::RESOLVE_FACTORY:
				return $this->resolveServiceFactory($serviceName);
			break;

			case static::RESOLVE_SHARED:
				return $this->resolveServiceShared($serviceName);
			break;
			
			default:
				throw new UnknownServiceException('Could not resolve service named "' . $serviceName . '", the resolver type is unkown.');
			break;
		}
	}

	/**
	 * Resolve a service from the given factory
	 * 
	 * @param ServiceFactoryInterface|Closure 			$factory
	 * @return mixed
	 */
	private function resolveServiceFromFactory($factory)
	{	
		if ($factory instanceof ServiceFactoryInterface)
		{
			return $factory->create($this);
		}
		elseif ($factory instanceof Closure)
		{
			return $factory($this);
		}

		// otherwise throw an exception 
		throw new InvalidServiceException('The service factory for the service named "'. $name .'" is invalid.');
	}

	/**
	 * Resolver that will store the returned value for the next
	 * access.
	 * 
	 * @param string 			$method
	 */
	protected function resolveServiceShared(string $name)
	{
		// already resolved?
		if (!isset($this->resolvedSharedServices[$name]))
		{
			$this->resolvedSharedServices[$name] = $this->resolveServiceFromFactory($this->resolverFactoriesShared[$name]);
		}

		// get the factory
		return $this->resolvedSharedServices[$name];
	}

	/**
	 * Resolver that will simply return the factoryies value 
	 * 
	 * @param string 			$method
	 */
	protected function resolveServiceFactory(string $name)
	{
		return $this->resolveServiceFromFactory($this->resolverFactories[$name]);
	}
}	