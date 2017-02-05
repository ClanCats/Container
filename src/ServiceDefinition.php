<?php 
namespace ClanCats\Container;

use ClanCats\Container\{
    Exceptions\InvalidServiceException
};

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
     * Construct a single service definition object from an array
     * 
     *     ServiceDefinition::fromArray([
     *         'class' => '\Acme\Demo',
     *         'arguments' => ['@foo', ':bar'],
     *         'calls' => [
     *             ['method' => 'setName', [':demo.name']]
     *         ]
     *     ])
     * 
     * @param array             $serviceConfiguration
     * @return static
     */
    public static function fromArray(array $serviceConfiguration)
    {
        if (!isset($serviceConfiguration['class']))
        {
            throw new InvalidServiceException('The service configuration must define a "class" attribute.');
        }

        // construct the service definition
        $defintion = new static($serviceConfiguration['class'], $serviceConfiguration['arguments'] ?? []);

        // add service method calls if configured
        if (isset($serviceConfiguration['calls']))
        {
            foreach($serviceConfiguration['calls'] as $call)
            {
                if (isset($call['method']) && isset($call['arguments']))
                {
                    $defintion->calls($call['method'], $call['arguments']);
                }
                else 
                {
                    throw new InvalidServiceException('Every service call must have the attributes "method" and "arguments".');
                }
            }
        }

        return $defintion;
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
		$this->constructorArguments = new ServiceArguments($arguments);
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
    public function arguments(array $arguments) : ServiceDefinition
    {
      	$this->constructorArguments->addArgumentsFromArray($arguments); return $this;
    }

	/**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addRawArgument($argumentValue) : ServiceDefinition
    {
      	$this->constructorArguments->addRaw($argumentValue); return $this;
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addDependencyArgument($argumentValue) : ServiceDefinition
    {
        $this->constructorArguments->addDependency($argumentValue); return $this;
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addParameterArgument($argumentValue) : ServiceDefinition
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
    public function calls(string $method, array $arguments = []) : ServiceDefinition
    {
    	return $this->addMethodCall($method, new ServiceArguments($arguments));
    }

    /**
     * Adds a method call to the service factory 
     * 
     * @param string 					$methodName
     * @param ServiceArguments 	$arguments
     * @return self
     */
    public function addMethodCall(string $methodName, ServiceArguments $arguments) : ServiceDefinition
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