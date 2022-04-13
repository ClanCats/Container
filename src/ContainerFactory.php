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
    Exceptions\ContainerException
};

class ContainerFactory 
{
    /**
     * Service loader file cache
     * 
     * @var string
     */
    protected string $cacheDirectory;

    /**
     * Is the factory in debug mode
     * 
     * @var bool
     */
    protected bool $debugMode = false;

    /**
     * Construct a new service loader with a given cache directory
     * 
     * @param string                $cacheDirectory
     * @return void
     */
    public function __construct(string $cacheDirectory, bool $debugMode = false)
    {
        $this->setCacheDirecotry($cacheDirectory);
        $this->debugMode = $debugMode;
    }

    /**
     * Check if the container factory is in debug mode.
     * 
     * @return bool
     */
    public function isDebugMode() : bool
    {
        return $this->debugMode;
    }

    /**
     * Set the cache directory 
     * 
     * @param string        $cacheDirectory
     * @return void
     */
    public function setCacheDirecotry(string $cacheDirectory) : void
    {
        if (substr($cacheDirectory, -1) !== DIRECTORY_SEPARATOR)
        {
            $cacheDirectory .= DIRECTORY_SEPARATOR;
        }

        $this->cacheDirectory = $cacheDirectory;
    }

    /**
     * Get the current cache directory 
     * 
     * @return string The cache directory used by the factory.
     */
    public function getCacheDirectory() : string
    {
        return $this->cacheDirectory;
    }

    /**
     * Create a container with the given name. You can pass an 
     * array with services
     * 
     * @param string                         $containerName
     * @param callable                       $builderCallback
     * @param array<string, mixed>           $initalParameters
     * 
     * @return Container The generated container instnace.
     */
    public function create(string $containerName, callable $builderCallback, array $initalParameters = []) : Container
    {
        if (class_exists($containerName))
        {
            return new $containerName;
        }

        $fileName = basename(str_replace("\\", '/', $containerName));
        $cacheFile = $this->cacheDirectory . $fileName . '.php';

        if ((!(file_exists($cacheFile) && is_readable($cacheFile))) || $this->isDebugMode())
        {
            $builder = new ContainerBuilder($containerName);

            // run the builder callback
            $builderCallback($builder);

            // store the cache file
            $cacheDir = dirname($cacheFile);

            if (!is_dir($cacheDir) || !is_writable($cacheDir)) {
                throw new ContainerException("The directory \"{$cacheDir}\" is not writable. Cannot generate container class.");
            } elseif (is_file($cacheFile) && !is_writable($cacheFile)) {
                throw new ContainerException("The file \"{$cacheFile}\" is not writable. Cannot generate container class.");
            }

            file_put_contents($cacheFile, $builder->generate());
        }

        // require the generated cache file
        require_once $cacheFile;

        // create an instance of the generated container
        return new $containerName($initalParameters);
    }
}
