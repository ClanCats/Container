<?php 
namespace ClanCats\Container;

/**
 * Im honestly just bad at naming.. 
 */
class ServiceLoaderService
{
	/**
	 * The service class name
	 * 
	 * @var string
	 */
	protected $className;

	/**
	 * Is the instance shared?
	 * 
	 * @var bool
	 */
	protected $isShared = true;

	/**
	 * Construct a new service loader with a given cache directory
	 * 
	 * @param string 				$cacheDirectory
	 * @return void
	 */
	public function __construct(string $className)
	{
		$this->className = $className;
	}

	/**
	 * Retrun the services class name string
	 */
	public function getClassName() : string
	{
		return $this->className;
	}

	/**
	 * Is the current service shared?
	 * 
	 * @return bool
	 */
	public function isShared() : bool
	{
		return $this->isShared;
	}

	/**
	 * Set if the current service is shared
	 * 
	 * @param bool 			$shared
	 * @return void
	 */
	public function setShared(bool $shared) : void
	{
		$this->isShared = $shared;
	}
}