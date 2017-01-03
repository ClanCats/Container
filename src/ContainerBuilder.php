<?php 
namespace ClanCats\Container;

use ClanCats\Container\{
	Container
};

class ContainerBuilder 
{
	/**
	 * The container name
	 * 
	 * @var string
	 */
	protected $containerName;

	/**
	 * An array of binded services
	 * 
	 * @param array[string => Service]
	 */
	protected $services = [];

	/**
	 * Constrcut a container builder instance 
	 * 
	 * @param string 			$containerName
	 * @return void
	 */
	public function __construct(string $containerName)
	{
		$this->containerName = $containerName;
	}

	/**
	 * Add a service 
	 */
	public function add()
	{

	}

	/**
	 * 
	 */
	public function addService(string $serviceName, ServiceDefinitionInterface $serviceDefinition)
	{
		$this->services[$serviceName] = $serviceDefinition;
	}

	/**
	 * Generate the container class code string
	 * 
	 * @return string
	 */
	public function generate() : string
	{
		return "<?php class $this->containerName extends " . Container::class . ' {}';
	}
}