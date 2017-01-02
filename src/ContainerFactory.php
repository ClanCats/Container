<?php 
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
	protected $cacheDirectory = null;

	/**
	 * Is the factory in debug mode
	 * 
	 * @var bool
	 */
	protected $debugMode = false;

	/**
	 * Construct a new service loader with a given cache directory
	 * 
	 * @param string 				$cacheDirectory
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
		return $this->isDebugMode();
	}

	/**
	 * Set the cache directory 
	 * 
	 * @param string 		$cacheDirectory
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
	 * @param string 				$containerName
	 * @param 
	 * 
	 * @return Container The generated container instnace.
	 */
	public function create(string $containerName, $builderCallback) : Container
	{
		if (class_exists($containerName))
		{
			throw new ContainerException('The class "' . $containerName . '" is already registered!');
		}

		$cacheFile = $this->cacheDirectory . $containerName . '.php';

		if (!(file_exists($cacheFile) && is_readable($cacheFile) || $this->isDebugMode())
		{

		}

		// require the generated cache file
		require $cacheFile;

		// create an instance of the generated container
		return new \$containerName;
	}
}