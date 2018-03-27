<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2018 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

use Closure;
use ClanCats\Container\{

    // exceptions
    Exceptions\UnknownServiceException,
    Exceptions\InvalidServiceException,
    Exceptions\ContainerException
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
    const RESOLVE_PROVIDER = 1;
    const RESOLVE_FACTORY = 2;
    const RESOLVE_SHARED = 3;
    const RESOLVE_SETTER = 4;
    protected $serviceResolverType = [];

    /**
     * An array of services and their provider
     * 
     * @var array[string => ServiceProviderInterface]
     */
    protected $serviceProviders = [];

    /**
     * An array of methods that resolve a service inside the current container.
     * This is mostly for the cached / dumped container which creates custom methods
     * for each service to (try to) improve performance of the container.
     * 
     * @var array
     */
    protected $resolverMethods = [];

    /**
     * An array of factories ojects.
     * 
     * @var array[ServiceFactoryInterface|Closure]
     */
    private $resolverFactories = [];

    /**
     * Array of already resolved shared factories
     * 
     * @var array
     */
    protected $resolvedSharedServices = [];

    /**
     * The actual metadata by key & service name
     *
     *    [metakey][service] = [[meta array], [meta array]]
     * 
     * @var array[string => array]
     */
    protected $metadata = [];

    /**
     * List of metadata keys by service name
     *
     *    [service] = [metakey array]
     *
     * @var string[array]
     */
    protected $metadataService = [];

    /**
     * Construct a new container instance with inital parameters.
     * 
     * @param array                 $initalParameters Array of inital parameters.
     * @return void
     */
    public function __construct(array $initalParameters = [])
    {
        $this->parameters = array_merge($this->parameters, $initalParameters);
    }

    /**
     * Does the container contain the parameter with the given name?
     * 
     * @param string            $name The parameter name.
     * @return bool
     */
    public function hasParameter(string $name) : bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Get the parameter with the the given name, or return the default 
     * value if the parameter is not set.
     * 
     * @param string            $name The parameter name.
     * @param mixed             $default The returned default value if the parameter is not set. 
     * @return mixed 
     */
    public function getParameter(string $name, $default = null)
    {
        return $this->hasParameter($name) ? $this->parameters[$name] : $default;
    }

    /**
     * Set the given parameter with value
     * 
     * @param string            $name The parameter name.
     * @param mixed             $value The parameter value.
     * @return void
     */
    public function setParameter(string $name, $value) 
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Get the metadata of a specific service
     *
     * @param string            $serviceName
     */
    public function getMetaData(string $serviceName, string $key) : array
    {
        return $this->metadata[$key][$serviceName] ?? [];
    }

    /**
     * Get the metadata keys of the given service
     *
     * @param string            $serviceName
     */
    public function getMetaDataKeys(string $serviceName) : array
    {
        return $this->metadataService[$serviceName];
    }

    /**
     * Make sure the metadata and key are linked both ways.
     *
     * @param string            $serviceName
     * @param string            $key
     * @return void 
     */
    private function linkMetaService(string $serviceName, string $key) 
    {
        // make sure the service link is there
        if (!isset($this->metadataService[$serviceName])) {
            $this->metadataService[$serviceName] = [];
        }

        if (!in_array($key, $this->metadataService[$serviceName])) {
            $this->metadataService[$serviceName][] = $key; 
        }
    }

    /**
     * Set metadata for a specific service
     * This will override all meta data matching service and key.
     *
     * @param string            $serviceName
     * @param string            $key
     * @param array             $values multidimensional array
     *
     * @return void
     */
    public function setMetaData(string $serviceName, string $key, array $values)
    {
        // make sure the service exists
        if (!$this->has($serviceName)) { 
            throw new UnknownServiceException('There is no service named "' . $serviceName . '" specified.'); 
        }

        // make sure all elements are arrays
        foreach($values as $value) 
        {
            if (!is_array($value)) {
                throw new ContainerException('Every meta data value must be an array. "' . gettype($value) . '" given.');
            }
        }

        // make sure the metadata key is allocated
        if (!isset($this->metadata[$key])) {
            $this->metadata[$key] = [];
        }

        // write
        $this->metadata[$key][$serviceName] = $values;
        $this->linkMetaService($serviceName, $key);
    }

    /**
     * Same as `setMetaData` but will append the data instead of overriding.
     *
     * @param string            $serviceName
     * @param string            $key
     * @param array             $values
     *
     * @return void
     */
    public function addMetaData(string $serviceName, string $key, array $values)
    {
        // make sure the service exists
        if (!$this->has($serviceName)) {
            throw new UnknownServiceException('There is no service named "' . $serviceName . '" specified.'); 
        }

        // make sure the metadata key is allocated
        if (!isset($this->metadata[$key])) {
            $this->metadata[$key] = [];
        }

        // make sure the service key is allocated
        if (!isset($this->metadata[$key][$serviceName])) {
            $this->metadata[$key][$serviceName] = [];
        }

        // append
        $this->metadata[$key][$serviceName][] = $values;
        $this->linkMetaService($serviceName, $key);
    }

    /**
     * Get an array of service names that have metadata with the given key
     *
     * @param string            $key The metadata key
     * @return array
     */
    public function serviceNamesWithMetaData(string $key) : array
    {
        return $this->metadata[$key] ?? []; 
    }


    /**
     * Returns an array of all available service keys.
     * 
     * @return array[string]
     */
    public function available() : array
    {
        $available = array_keys($this->serviceResolverType);
        $available[] = 'container';

        return $available;
    }

    /**
     * Does the container have the given service?
     * 
     * @param string            $serviceName The name / Identifier of the service to look for.
     * @return bool
     */
    public function has(string $serviceName) : bool
    {
        return $serviceName === 'container' || isset($this->serviceResolverType[$serviceName]);
    }

    /**
     * Sets a value on the container instance. 
     * This will overwrite any service stored / shared under the same name.
     * 
     * @param string            $serviceName The name / Identifier of the service to look for.
     * @param mixed             $serviceValue
     * @return void
     */
    public function set(string $serviceName, $serviceValue) 
    {
        if ($serviceName === 'container')
        {
            throw new ContainerException('Cannot overwrite self container reference!');
        }

        $this->resolvedSharedServices[$serviceName] = $serviceValue;
        $this->setServiceResolverType($serviceName, static::RESOLVE_SETTER);
    }

    /**
     * Removes a service from the container and releases the shared instance
     * if it has been loaded.
     * 
     * @param string            $serviceName The name / Identifier of the service to look for.
     * 
     * @return bool Returns true if the service has been removed.
     */
    public function remove(string $serviceName) : bool
    {
        if (!$this->has($serviceName)) {
            return false; // can only remove services that exist
        }

        if ($serviceName === 'container') {
            return false; // the container itself cannot be removed
        }

        // remove the already shared instnace if set
        if (isset($this->resolvedSharedServices[$serviceName])) {
            unset($this->resolvedSharedServices[$serviceName]);
        }

        // remove the service type
        if (isset($this->serviceResolverType[$serviceName])) {
            unset($this->serviceResolverType[$serviceName]);
        }

        // remove all possible references
        if (isset($this->serviceProviders[$serviceName])) {
            unset($this->serviceProviders[$serviceName]);
        }
        if (isset($this->resolverMethods[$serviceName])) {
            unset($this->resolverMethods[$serviceName]);
        }
        if (isset($this->resolverFactories[$serviceName])) {
            unset($this->resolverFactories[$serviceName]);
        }

        return true;
    }

    /**
     * Check if the given service has already been resolved / shared / initiated.
     * A factory service will always return false.
     * 
     * @param string            $serviceName The name / Identifier of the service to look for.
     * @return bool
     */
    public function isResolved(string $serviceName) : bool
    {
        if ($serviceName === 'container') {
            return true; // the container itself is always resolved
        }

        if (!isset($this->serviceResolverType[$serviceName])) {
            return false; 
        }

        if ($this->serviceResolverType[$serviceName] === static::RESOLVE_FACTORY) {
            return false;
        }

        return isset($this->resolvedSharedServices[$serviceName]);
    }

    /**
     * Release a shared resolved service from the container. This 
     * will force the service to reload when accessed again.
     * 
     * @param string                $serviceName
     * 
     * @return bool Return false on failure.
     */
    public function release(string $serviceName) : bool
    {
        if (isset($this->resolvedSharedServices[$serviceName]))
        {
            unset($this->resolvedSharedServices[$serviceName]); return true;
        }

        return false;
    }

    /**
     * Retrieve a service from the container. 
     * 
     * @throws UnknownServiceException When a service could not be found or is unresolvable.
     * 
     * @param string            $serviceName The name / Identifier of the service to look for.
     * 
     * @return mixed The requested service.
     */
    public function get(string $serviceName)
    {
        // if the service container itself is requested
        if ($serviceName === 'container') { 
            return $this;
        }

        // check if the service name has a registered service type
        if (!isset($this->serviceResolverType[$serviceName]))
        {
            throw new UnknownServiceException('Could not find service named "' . $serviceName . '" registered in the container.');
        }
        $serviceResolverType = $this->serviceResolverType[$serviceName];

        // check if a service instance already exists
        if (
            isset($this->resolvedSharedServices[$serviceName]) && 
            $serviceResolverType !== static::RESOLVE_FACTORY
        ) {
            return $this->resolvedSharedServices[$serviceName];
        }

        switch ($serviceResolverType)
        {
            // Default resolver for all services that are defined 
            // directly in the container. We can skip here an unnecessary method call.
            case static::RESOLVE_METHOD:
                return $this->{$this->resolverMethods[$serviceName]}();
            break;

            case static::RESOLVE_PROVIDER:
                return $this->resolveServiceProvider($serviceName);
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
     * Resolve a service instance from the given factory object.
     * 
     * @param ServiceFactoryInterface|Closure           $factory The factory object.
     * 
     * @return mixed The instance of created by the factory object.
     */
    private function createInstanceFromFactory($factory)
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
        throw new InvalidServiceException('Service could not be resolved, the registered factory is invalid.');
    }

    /**
     * Resolves a service instance from a provider and stores the 
     * result inside the shared services if needed.
     * 
     * @param string            $name The service name.
     * 
     * @return mixed The service instance created by the provider.
     */
    private function resolveServiceProvider(string $name)
    {
        list($service, $isShared) = $this->serviceProviders[$name]->resolve($name, $this);

        if ($isShared) {
            $this->resolvedSharedServices[$name] = $service;
        }

        return $service;
    }

    /**
     * Resolver that will always store the returned value inside the service container.
     * 
     * @param string            $name The service name.
     * 
     * @return mixed The service instance created by the factory or shared in the container.
     */
    private function resolveServiceShared(string $name)
    {
        return $this->resolvedSharedServices[$name] = $this->resolveServiceFactory($name);
    }

    /**
     * Resolver that will simply create an instance from the factory.
     * 
     * @param string            $name The service name.
     * 
     * @return mixed The service instance created by the factory.
     */
    private function resolveServiceFactory(string $name)
    {
        return $this->createInstanceFromFactory($this->resolverFactories[$name]);
    }

    /**
     * Register a service provider.
     * This will call the `provides` method on the given service provider instance.
     * 
     * @param ServiceProviderInterface          $provider The service provider instance.
     * @return void
     */
    public function register(ServiceProviderInterface $provider) 
    {
        foreach($provider->provides() as $serviceName)
        {
            $this->setServiceResolverType($serviceName, static::RESOLVE_PROVIDER);
            $this->serviceProviders[$serviceName] = $provider;
        }
    }

    /**
     * Binds a service factory to the container.
     * 
     *     $container->bind('session', new SessionFactory);
     * 
     *     $container->bind('config', function($c) {
     *          return new Config($c->get('config.loader'));
     *     }, false);
     * 
     *     $container->bind('router', '\\Routing\\Router')
     *         ->addDependencyArgument('config');
     * 
     * @param string            $name The service name.
     * @param mixed             $factory The service factory instance, the closure or the classname as string
     * @param bool              $shared Should the service be shared inside the container.
     * 
     * @return Closure|ServiceFactoryInterface The given or generated service factory.
     */
    public function bind(string $name, $factory, bool $shared = true)
    {
        if (is_string($factory)) {
            return $this->bindClass($name, $factory, [], $shared);
        } elseif ($shared) {
            $this->bindFactoryShared($name, $factory);
        } else {
            $this->bindFactory($name, $factory);
        }
    }

    /**
     * Creates and binds a service factory by class name and arguments. 
     * 
     * @param string            $name The service name.
     * @param string            $className The service class name.
     * @param array             $arguments An array of arguments.
     * @param bool              $shared Should the service be shared inside the container.
     * 
     * @return ServiceFactory The created service factory
     */
    public function bindClass(string $name, string $factory, array $arguments = [], bool $shared = true) : ServiceFactory
    {
        $factory = new ServiceFactory($factory, $arguments);

        if ($shared) {
            $this->bindFactoryShared($name, $factory);
        } else {
            $this->bindFactory($name, $factory);
        }

        return $factory;
    }

    /**
     * Binds an unshared factory instance to the service container.
     *
     * @param string                            $name The service name.
     * @param ServiceFactoryInterface|Closure   $factory The service factory instance or closure.
     * @return void
     */
    public function bindFactory(string $name, $factory) 
    {
        $this->setServiceResolverType($name, static::RESOLVE_FACTORY);
        $this->resolverFactories[$name] = $factory;
    }

    /**
     * Binds a shared factory instance to the service container.
     * 
     * @param string                            $name The service name.
     * @param ServiceFactoryInterface|Closure   $factory The service factory instance or closure.
     * @return void
     */
    public function bindFactoryShared(string $name, $factory) 
    {
        $this->setServiceResolverType($name, static::RESOLVE_SHARED);
        $this->resolverFactories[$name] = $factory;
    }

    /**
     * Set a service resolver type.
     * The service resolver type tells the container where to look for the correct resolver.
     * 
     * @param string            $serviceName The name / Identifier of the service to look for.
     * @param int               $serviceType The service type as int represented by the `RESOLVE_` prefixed constants.
     * @return void
     */
    private function setServiceResolverType(string $serviceName, int $serviceType) 
    {
        $this->serviceResolverType[$serviceName] = $serviceType;
    }

    /**
     * Get the resolver type of the given service name.
     * 
     * @param string                $serviceName
     * @return int
     */
    public function getServiceResolverType(string $serviceName) : int
    {
        if (!isset($this->serviceResolverType[$serviceName]))
        {
            throw new UnknownServiceException('There is no type for the service named "' . $serviceName . '" specified.');
        }

        return $this->serviceResolverType[$serviceName];
    }
}   