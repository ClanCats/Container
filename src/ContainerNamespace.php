<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2017 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

use ClanCats\Container\{
    Exceptions\ContainerNamespaceException
};

use ClanCats\Container\ContainerParser\{
    ContainerParser
};

class ContainerNamespace
{
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
     * @return array
     */ 
    public function parse(string $containerFilePath) : array
    {
        $parser = new ContainerParser($this->getCodeFromFile($containerFilePath), $this);
    }
}