<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2018 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
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
     *        ->addDependencyArgument('storage')
     *        ->addParameterArgument('session_token')
     *        ->addRawArgument(600)
     * 
     * Or the shorter way
     *     
     *     ServiceDefinition::for('\Acme\SessionService', ['@storage', ':session_token', 600])
     * 
     * @param string            $serviceClassName The full class name of the desired service.
     * @param array             $arguments An array of constructor arguments for the service.     
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
     * @var array[[<method name>, ServiceArguments]]
     */
    protected $methodCallers = [];

    /**
     * Meta data for this service definition
     *
     * @var array[string => array]  
     */
    protected $metaData = [];

    /**
     * Construct a new service definition with the given classname and optional arguments as array.
     * 
     * @param string            $className The full class name of the desired service.
     * @param array             $arguments An array of constructor arguments for the service.  
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
     * Add an array of arguments to the service constructor
     * 
     * @param array             $arguments
     * @return self
     */
    public function arguments(array $arguments) : ServiceDefinition
    {
        $this->constructorArguments->addArgumentsFromArray($arguments); return $this;
    }

    /**
     * Add a simple raw constructor argument. 
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addRawArgument($argumentValue) : ServiceDefinition
    {
        $this->constructorArguments->addRaw($argumentValue); return $this;
    }

    /**
     * Add a dependency constructor argument.
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addDependencyArgument($argumentValue) : ServiceDefinition
    {
        $this->constructorArguments->addDependency($argumentValue); return $this;
    }

    /**
     * Add a simply parameter constructor argument.
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
     * Adds a method call to the service definition, the arguments should be set as an array.
     * 
     * @param string            $method The name of the method to be called.
     * @param array             $arguments The method arguments as array.
     * @return self
     */
    public function calls(string $method, array $arguments = []) : ServiceDefinition
    {
        return $this->addMethodCall($method, new ServiceArguments($arguments));
    }

    /**
     * Adds a method call to the service definition, the arguments must be set with a ServiceArguments instance.
     * 
     * @param string                    $methodName The name of the method to be called.
     * @param ServiceArguments          $arguments An `ServiceArguments` instance for the method call.
     * @return self
     */
    public function addMethodCall(string $methodName, ServiceArguments $arguments) : ServiceDefinition
    {
        $this->methodCallers[] = [$methodName, $arguments]; return $this;
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
     * Sets meta data for the given key
     *
     * @param string        $key
     * @param array         $values
     * @return self
     */
    public function addMetaData(string $key, array $values)
    {
        if (!isset($this->metaData[$key])) {
            $this->metaData[$key] = [];
        }

        $this->metaData[$key][] = $values; return $this;
    }

    /**
     * Get all metadata of the service definition
     *
     * @return array 
     */
    public function getMetaData() : array
    {
        return $this->metaData;
    }
}   