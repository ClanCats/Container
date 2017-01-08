<?php 
declare(strict_types=1);

namespace ClanCats\Container;

use ClanCats\Container\{
	Container,

	Exceptions\ContainerBuilderException
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
	 * An array of service names that should be shared in the builded container
	 * 
	 * @param array[string]
	 */
	protected $shared = [];

	/**
	 * Constrcut a container builder instance 
	 * 
	 * @param string 			$containerName
	 * @return void
	 */
	public function __construct(string $containerName)
	{
		if ($this->validateNonNumericString($containerName))
		{
			throw new ContainerBuilderException('The container name cannot be empty.');
		}

		$this->containerName = $containerName;
	}

	/**
	 * Get the current container name
	 * 
	 * @return string 
	 */
	public function getContainerName() : string
	{
		return $this->containerName;
	}

	/**
	 * Get all currently added services 
	 * 
	 * @return array[string => ServiceDefinition]
	 */
	public function getServices() : array 
	{
		return $this->services;
	}

	/**
	 * Returns all shared service names
	 * 
	 * @return array[string]
	 */
	public function getSharedNames() : array 
	{
		return $this->shared;
	}

	/**
	 * Add a service by string and arguments array.
	 * 
	 * @param string 			$serviceName
	 * @param string 			$serviceClass
	 * @param array 			$serviceArguments
	 * @param bool 				$shared
	 * @return ServiceDefinition
	 */
	public function add(string $serviceName, string $serviceClass, array $serviceArguments = [], bool $isShared = true) : ServiceDefinition
	{
		$service = ServiceDefinition::for($serviceClass, $serviceArguments);
		$this->addService($serviceName, $service, $isShared);

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
			$this->addService($serviceName, ServiceDefinition::fromArray($serviceConfiguration), $serviceConfiguration['shared'] ?? true);
		}
	}

	/**
	 * Add a service definition instance to the container builder.
	 * 
	 * @param string 						$serviceName
	 * @param ServiceDefinitionInterface	$serviceDefinition
	 * @return void
	 */
	public function addService(string $serviceName, ServiceDefinitionInterface $serviceDefinition, bool $isShared = true) : void
	{
		if ($this->validateNonNumericString($serviceName))
		{
			throw new ContainerBuilderException('The servicename must be a string and cannot be numeric or empty.');
		}

		$this->services[$serviceName] = $serviceDefinition;

		if ($isShared && (!in_array($serviceName, $this->shared)))
		{
			$this->shared[] = $serviceName;
		} 
		elseif ((!$isShared) && in_array($serviceName, $this->shared))
		{
			unset($this->shared[array_search($serviceName, $this->shared)]);
		}
	}

	/**
	 * Trhows exception if the value is empty or numeric
	 * 
	 * @param string 			$value
	 * @return bool
	 */
	private function validateNonNumericString(string $value) : bool
	{
		return empty($value) || is_numeric($value);
	}

	/**
	 * Generate the container class code string
	 * 
	 * @return string
	 */
	public function generate() : string
	{
		$buffer = "<?php \nclass $this->containerName extends " . Container::class . " {\n\n";

		$buffer .= $this->generateResolverTypes() . "\n";

		$buffer .= $this->generateResolverMappings() . "\n";

		$buffer .= $this->generateResolverMethods() . "\n";

		return $buffer . "\n}";
	}

	private function generateArgumentsCode(ServiceArguments $arguments)
	{
		$buffer = [];

		foreach($arguments->getAll() as list($argumentValue, $argumentType))
		{
			if (in_array($argumentType, [ServiceArguments::DEPENDENCY, ServiceArguments::PARAMETER]) && $this->validateNonNumericString($argumentValue))
			{
				throw new ContainerBuilderException('Parameter and dependency arguments must be non numeric and not empty to be builded.');
			}

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
				$buffer[] = var_export($argumentValue, true);
			}
		}

		return implode(', ', $buffer);
	}

	private function generateResolverTypes() : string
	{
		$types = []; 

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$types[] = var_export($serviceName, true) . ' => ' . Container::RESOLVE_METHOD;
		}

		return "protected \$serviceResolverType = [" . implode(', ', $types) . "];\n";
	}

	private function generateResolverMappings() : string
	{
		$mappings = []; 

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$mappings[] = var_export($serviceName, true) . ' => ' . var_export('resolve' . $this->camelize($serviceName), true);
		}

		return "protected \$resolverMethods = [" . implode(', ', $mappings) . "];\n";
	}

	private function generateResolverMethods() : string
	{
		$buffer = "";

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$buffer .= "protected function resolve" . $this->camelize($serviceName) . "() {\n";

			$buffer .= "\t\$instance = new " . $serviceDefinition->getClassName() . "(". $this->generateArgumentsCode($serviceDefinition->getArguments()) .");\n";

			foreach($serviceDefinition->getMethodCalls() as $callName => $callArguments)
			{
				$buffer .= "\t\$instance->" . $callName . '('. $this->generateArgumentsCode($callArguments) .");\n";
			}

			if (in_array($serviceName, $this->shared))
			{
				$buffer .= "\t\$this->resolvedSharedServices[" . var_export($serviceName, true) . "] = \$instance;\n";
			}

			$buffer .= "\treturn \$instance;\n";

			$buffer .= "}\n";
		}

		return $buffer;
	}

	private function camelize($input) : string
	{
		$input = str_replace([' ', '_'], '.', $input);
	    return str_replace('.', '', ucwords($input, '.'));
	}
}