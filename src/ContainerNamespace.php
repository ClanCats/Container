<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2022 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

use ClanCats\Container\{
    Exceptions\ContainerNamespaceException,
    ServiceDefinition
};

use ClanCats\Container\ContainerParser\{
    ContainerLexer,
    ContainerInterpreter,
    Parser\ScopeParser
};

/**
 * The container namespace acts as a collection of multiple 
 * container files that get parsed into one pot.
 */
class ContainerNamespace
{
    /**
     * The container namespaces parameters
     * 
     * @var array
     */
    protected $parameters = [];

    /**
     * The container service aliases
     * 
     * @var array
     */
    protected $aliases = [];

    /**
     * The container namespaces service defintions
     * 
     * @param array[string => Service]
     */
    protected $services = [];

    /**
     * An array of service names that should be shared through the container
     * 
     * @param array[string]
     */
    protected $shared = [];

    /**
     * An array of paths 
     * 
     *     name => container file path
     * 
     * @var array
     */
    protected $paths = [];

    /**
     * Constructor
     * 
     * @param $paths array[string:string]   
     */
    public function __construct(array $paths = [])
    {
        $this->paths = $paths;
    }

    /**
     * Import paths from vendor container map
     *
     * @param string                $vendorDir
     * @return void
     */
    public function importFromVendor(string $vendorDir)
    {
        $mappingFile = $vendorDir . '/container_map.php';

        if (!(file_exists($mappingFile) && is_readable($mappingFile)))
        {
            throw new ContainerNamespaceException("Could not find the the container map file at: " . $mappingFile);
        }

        $vendorPaths = require $mappingFile;
        $this->paths = array_merge($vendorPaths, $this->paths);
    }

    /**
     * Does the container namespace have a parameter with the given name?
     * 
     * @param string            $name The parameter name.
     * @return bool
     */
    public function hasParameter(string $name) : bool
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * Set the given parameter and value
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
     * Get all parameters from the container namespace
     * 
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * Does the container namespace have an alias with the given name?
     * 
     * @param string            $name The alias name.
     * @return bool
     */
    public function hasAlias(string $name) : bool
    {
        return array_key_exists($name, $this->aliases);
    }

    /**
     * Set the given alias and target
     * 
     * @param string            $name The alias name.
     * @param mixed             $target The alias target.
     * @return void
     */
    public function setAlias(string $name, $target) 
    {
        $this->aliases[$name] = $target;
    }

    /**
     * Get all aliases from the container namespace
     * 
     * @return array
     */
    public function getAliases() : array
    {
        return $this->aliases;
    }

    /**
     * Does the container namespace have a service with the given name?
     * 
     * @param string            $name
     * @return bool
     */
    public function hasService(string $name) : bool
    {
        return array_key_exists($name, $this->services);
    }

    /**
     * Set a service in the namespace
     * 
     * @param string            $name The service name.
     * @param mixed             $value The service definition.
     * @return void
     */
    public function setService(string $name, ServiceDefinition $service) 
    {
        $this->services[$name] = $service;
    }

    /**
     * Get all services from the container namespace
     * 
     * @return array[ServiceDefinition]
     */
    public function getServices() : array
    {
        return $this->services;
    }

    /**
     * Is the given path name binded?
     * 
     * @param string            $name The container files path key.
     * @return bool
     */
    public function has(string $name) : bool
    {
        return isset($this->paths[$name]) && is_string($this->paths[$name]);
    }

    /**
     * Simply returns the contents of the given file
     * 
     * @param return string         $containerFilePath The path to a container file.
     * @return string
     */
    protected function getCodeFromFile(string $containerFilePath) : string
    {
        if (!file_exists($containerFilePath) || !is_readable($containerFilePath))
        {
            throw new ContainerNamespaceException("The file '" . $containerFilePath . "' is not readable or does not exist.");
        }

        return file_get_contents($containerFilePath);
    }

    /**
     * Returns the code of in the current namespace binded file.
     * 
     * @return string           $name The container files path key.
     */
    public function getCode(string $name) : string
    {
        if (!$this->has($name))
        {
            throw new ContainerNamespaceException("There is no path named '" . $name . "' binded to the namespace.");
        }

        return $this->getCodeFromFile($this->paths[$name]);
    }

    /**
     * Parse the given container file with the current namespace
     * 
     * @param string        $containerFilePath The path to a container file.
     */ 
    public function parse(string $containerFilePath)
    {
        // create a lexer from the given file
        $lexer = new ContainerLexer($this->getCodeFromFile($containerFilePath), $containerFilePath);

        // parse the file
        $parser = new ScopeParser($lexer->tokens());

        // interpret the parsed node
        $interpreter = new ContainerInterpreter($this);
        $interpreter->handleScope($parser->parse());
    }
}
