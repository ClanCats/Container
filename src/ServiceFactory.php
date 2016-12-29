<?php 
namespace ClanCats\Container;

class ServiceFactory implements ServiceFactoryInterface 
{
	/**
	 * Static instance constructor to allow eye candy like:
	 * 
	 *     ServiceFactory::for('\Acme\SessionService')
	 * 	      ->addDependencyArgument('storage')
	 * 		  ->addParameterArgument('session_token')
	 * 		  ->addRawArgument(600)
	 * 
	 * Or the shorter way
	 * 
	 *     ServiceFactory::for('\Acme\SessionService', ['@storage', ':session_token', 600])
	 */
	public static function for(string $serviceClassName, array $arguments = []) : ServiceFactory
	{
		return new static($serviceClassName, $arguments);
	}

	/**
	 * The services class name
	 */
	protected $className;

	/**
	 * The consturcor arguments of the service
	 * 
	 * @var ServiceFactoryArguments
	 */
	protected $constructorArguments;

	/**
	 * An array of method calls after service construction
	 * 
	 * @var array[string => ServiceFactoryArguments]
	 */
	protected $methodCallers = [];

	/**
	 * Construct a new service loader with a given cache directory
	 * 
	 * @param string 				$cacheDirectory
	 * @return void
	 */
	public function __construct(string $className, array $arguments = [])
	{
		$this->className = $className;
		$this->constructorArguments = ServiceFactoryArguments::from($arguments);
	}

	/**
	 * Returns the service class name
	 * 
	 * @return string
	 */
	public function getClassName() : string
	{
		return $this->className;
	}

	/**
     * Add an array of arguments to the service construcotr
     * 
     * @param array             $arguments
     * @return self
     */
    public function arguments(array $arguments) : ServiceFactory
    {
      	$this->constructorArguments->addArgumentsFromArray($arguments); return $this;
    }

	/**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addRawArgument($argumentValue) : ServiceFactory
    {
      	$this->constructorArguments->addRaw($argumentValue); return $this;
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addDependencyArgument($argumentValue) : ServiceFactory
    {
        $this->constructorArguments->addDependency($argumentValue); return $this;
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addParameterArgument($argumentValue) : ServiceFactory
    {
        $this->constructorArguments->addParameter($argumentValue); return $this;
    }

    /**
     * Returns the constructor arguments object
     * 
     * @return ServiceFactoryArguments
     */
    public function getArguments() : ServiceFactoryArguments
    {
    	return $this->constructorArguments;
    }

    /**
     * Adds a method call to the service factory
     * 
     * @param string 			$method
     * @param array 			$arguments
     * @return self
     */
    public function calls(string $method, array $arguments = []) : ServiceFactory
    {
    	return $this->addMethodCall($method, ServiceFactoryArguments::from($arguments));
    }

    /**
     * Adds a method call to the service factory 
     * 
     * @param string 					$methodName
     * @param ServiceFactoryArguments 	$arguments
     * @return self
     */
    public function addMethodCall(string $methodName, ServiceFactoryArguments $arguments) : ServiceFactory
    {
    	$this->methodCallers[$methodName] = $arguments; return $this;
    }

    /**
     * Returns all registered method calls
     * 
     * @return array
     */
    public function getMethodCalls() : array
    {
    	return $this->methodCallers;
    }

	/**
	 * Construct your object, or value based on the given container.
	 * 
	 * @param Container 			$container
	 * @return mixed
	 */
	public function create(Container $container)
	{
		$instance = new $this->className(...$this->constructorArguments->resolve($container)); 

		foreach($this->methodCallers as $method => $arguments)
		{
			$instance->{$method}(... $arguments->resolve($container));
		}

		return $instance;
	}
}	