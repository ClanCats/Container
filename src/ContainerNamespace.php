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
    Parser\ScopeParser,
    Nodes\ScopeNode
};
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * The container namespace acts as a collection of multiple 
 * container files that get parsed into one pot.
 */
class ContainerNamespace
{
    /**
     * The container namespaces parameters
     * 
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    /**
     * The container service aliases
     * 
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * The container namespaces service defintions
     * 
     * @var array<string, ServiceDefinitionInterface>
     */
    protected array $services = [];

    /**
     * An array of service names that should be shared through the container
     * 
     * @var array<string>
     */
    protected array $shared = [];

    /**
     * An array of paths 
     * 
     *     name => container file path
     * 
     * @var array<string, string>
     */
    protected array $paths = [];

    /**
     * Constructor
     * 
     * @param array<string, string>    $paths
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
    public function importFromVendor(string $vendorDir) : void
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
     * Recursivly imports all `ctn` files in the given directory into the namespace 
     * You can specify a prefix to be applied to each ctn file
     * 
     * @param string                $directory The directory path where to look for container files
     * @param string                $prefix optional prefix to be appiled to the import name
     * @param string                $fileExtension allows you to specify a custom file extension
     */
    public function importDirectory(string $directory, string $prefix = '', string $fileExtension = '.ctn') : void
    {
        // find available container files
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        $importPaths = [];
        foreach ($rii as $file) 
        {
            // skip directories
            if ($file->isDir()) continue;

            // skip non ctn files
            if (substr($file->getPathname(), -4) !== $fileExtension) continue;

            // get the import name
            $importName = trim($prefix . substr($file->getPathname(), strlen($directory), -(strlen($fileExtension))), '/');

            // add the file
            $importPaths[$importName] = $file->getPathname();
        }

        $this->paths = array_merge($this->paths, $importPaths);
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
    public function setParameter(string $name, $value) : void
    {
        $this->parameters[$name] = $value;
    }

    /**
     * Get all parameters from the container namespace
     * 
     * @return array<string, mixed>
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
     * @return array<string, string>
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
     * @param string                        $name The service name.
     * @param ServiceDefinitionInterface             $service The service definition.
     * @return void
     */
    public function setService(string $name, ServiceDefinitionInterface $service) : void
    {
        $this->services[$name] = $service;
    }

    /**
     * Get all services from the container namespace
     * 
     * @return array<string, ServiceDefinitionInterface>
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
        return isset($this->paths[$name]);
    }

    /**
     * Returns the system path to a file in the namespace
     * 
     * @param string            $name
     * @return string
     */
    public function getPath(string $name) : string 
    {
        return $this->paths[$name];
    }

    /**
     * Simply returns the contents of the given file
     * 
     * @param string         $containerFilePath The path to a container file.
     * @return string
     */
    protected function getCodeFromFile(string $containerFilePath) : string
    {
        if (!file_exists($containerFilePath) || !is_readable($containerFilePath))
        {
            throw new ContainerNamespaceException("The file '" . $containerFilePath . "' is not readable or does not exist.");
        }

        return file_get_contents($containerFilePath) ?: '';
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
    public function parse(string $containerFilePath) : void
    {
        // create a lexer from the given file
        $lexer = new ContainerLexer($this->getCodeFromFile($containerFilePath), $containerFilePath);

        // parse the file
        $parser = new ScopeParser($lexer->tokens());

        if (!(($node = $parser->parse()) instanceof ScopeNode)) {
            throw new ContainerNamespaceException("Scope parser returned an unexpeted node type: " . get_class($node));
        }

        // interpret the parsed node
        $interpreter = new ContainerInterpreter($this);
        $interpreter->handleScope($node);
    }
}
