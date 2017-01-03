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
	 * Add a service by string and arguments array.
	 * 
	 * @param string 			$serviceName
	 * @param array 			$serviceArguments
	 * @return ServiceDefinition
	 */
	public function add(string $serviceName, string $serviceClass, array $serviceArguments = []) : ServiceDefinition
	{
		$service = ServiceDefinition::for($serviceClass, $serviceArguments);
		$this->addService($serviceName, $service);

		return $service;
	}

	/**
	 * Add services by an array
	 * 
	 * @param array 			$serviceArray
	 * @return void
	 */
	public function addArray(array $servicesArray) : void
	{
		foreach($servicesArray as $serviceName => $serviceConfiguration)
		{
			$this->addService($serviceName, ServiceDefinition::fromArray($serviceConfiguration));
		}
	}

	/**
	 * Add a service definition instance to the container builder.
	 * 
	 * @param string 						$serviceName
	 * @param ServiceDefinitionInterface	$serviceDefinition
	 * @return void
	 */
	public function addService(string $serviceName, ServiceDefinitionInterface $serviceDefinition) : void
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
		$buffer = "<?php \nclass $this->containerName extends " . Container::class . " {\n\n";

		$buffer .= $this->generateResolverMethods() . "\n";

		return $buffer . "\n}";
	}

	private function generateArgumentsCode(ServiceArguments $arguments)
	{
		$buffer = [];

		foreach($arguments->getAll() as list($argumentValue, $argumentType))
		{
			if ($argumentType === ServiceArguments::DEPENDENCY)
			{
				$buffer[] = "\$this->get('$argumentValue')";
			}
			elseif ($argumentType === ServiceArguments::PARAMETER)
			{
				$buffer[] = "\$this->getParameter('$argumentValue')";
			}
			elseif ($argumentType === ServiceArguments::RAW)
			{
				$buffer[] = var_export($argumentsValue);
			}
		}


		return implode(', ', $buffer);
	}

	private function generateResolverMethods() : string
	{
		$buffer = "";

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$buffer .= "protected function resolve" . ucfirst($serviceName) . "() {\n";

			$buffer .= "\t\$instance = new " . $serviceDefinition->getClassName() . "(". $this->generateArgumentsCode($serviceDefinition->getArguments()) .");\n";

			$buffer .= "}\n";
		}

		return $buffer;
	}
}