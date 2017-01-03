<?php 
namespace ClanCats\Container;

class ServiceDefinition implements ServiceDefinitionInterface
{
	/**
	 * Static instance constructor to allow eye candy like:
	 * 
	 *     ServiceDefinition::for('\Acme\SessionService')
	 * 	      ->addDependencyArgument('storage')
	 * 		  ->addParameterArgument('session_token')
	 * 		  ->addRawArgument(600)
	 * 
	 * Or the shorter way
	 * 
	 *     ServiceDefinition::for('\Acme\SessionService', ['@storage', ':session_token', 600])
	 */
	public static function for(string $serviceClassName, array $arguments = [])
	{
		return new static($serviceClassName, $arguments);
	}

	/**
	 * The services class name
     * 
     * @var string 
	 */
	protected $className;

	/**
	 * The consturcor arguments of the service
	 * 
	 * @var ServiceArguments
	 */
	protected $constructorArguments;

	/**
	 * An array of method calls after service construction
	 * 
	 * @var array[string => ServiceArguments]
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
		$this->constructorArguments = ServiceArguments::from($arguments);
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
     * @return ServiceArguments
     */
    public function getArguments() : ServiceArguments
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
    	return $this->addMethodCall($method, ServiceArguments::from($arguments));
    }

    /**
     * Adds a method call to the service factory 
     * 
     * @param string 					$methodName
     * @param ServiceArguments 	$arguments
     * @return self
     */
    public function addMethodCall(string $methodName, ServiceArguments $arguments) : ServiceFactory
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
}	