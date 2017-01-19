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
	 * The full container name with namespace
	 * 
	 * @var string
	 */
	protected $containerName;

	/**
	 * The class name without namespace
	 * 
	 * @var string
	 */
	protected $containerClassName;

	/**
	 * Just the namespace
	 * 
	 * @var string
	 */
	protected $containerNamespace;

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
		$this->setContainerName($containerName);
	}

	/**
	 * Sets the container name 
	 * This will also update the "containerClassName" and "containerNamespace"
	 * 
	 * @param string 			$containerName
	 * @return void
 	 */
	public function setContainerName(string $containerName) : void
	{
		if ($this->invalidServiceBuilderString($containerName))
		{
			throw new ContainerBuilderException('The container name cannot be empty.');
		}

		if ($containerName[0] === "\\")
		{
			$containerName = substr($containerName, 1);
		}

		$this->containerClassName = $this->containerName = $containerName;

		// check if we need to generate a namespace
		if (($pos = strrpos($containerName, "\\")) !== false)
		{
			$this->containerNamespace = substr($containerName, 0, $pos);
			$this->containerClassName = substr($containerName, $pos + 1);
		}
	}

	/**
	 * Get the current container full name
	 * 
	 * @return string 
	 */
	public function getContainerName() : string
	{
		return $this->containerName;
	}

	/**
	 * Get the current container class name without namespace
	 * 
	 * @return string
	 */
	public function getContainerClassName() : string
	{
		return $this->containerClassName;
	}

	/**
	 * Get the current container namespace
	 * 
	 * @return string|null
	 */
	public function getContainerNamespace()
	{
		return $this->containerNamespace;
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
		$service = new ServiceDefinition($serviceClass, $serviceArguments);
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
		if ($this->invalidServiceBuilderString($serviceName))
		{
			throw new ContainerBuilderException('The "'.$serviceName.'" servicename must be a string and cannot be numeric or empty.');
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
	 * Checks if the given string is valid and not numeric &
	 * 
	 * @param string 			$value
	 * @return bool
	 */
	private function invalidServiceBuilderString(string $value) : bool
	{
		if (empty($value) || is_numeric($value)) {
			return true;
		}

		// check for trailing / prepending whitespace ect.
		if (trim($value) !== $value) {
			return true;
		}

		// check for other special characters
		if (preg_match('/[^a-zA-Z0-9._]+/', $value))  {
			return true;
		}

		// also check if the string contains with a number
		if (is_numeric($value[0]) || $value[0] === '.' || $value[0] === '_') {
			return true;
		}

		// check for doubled spacial characters
		// if (preg_replace('/,+/', ',', rtrim($value, ',')) !== $value) {

		// }

		return false;
	}

	/**
	 * Generate the container class code string
	 * 
	 * @return string
	 */
	public function generate() : string
	{
		$buffer = "<?php\n\n";

		// add namespace if needed
		if (!is_null($this->containerNamespace))
		{
			$buffer .= "namespace " . $this->containerNamespace . ";\n\n";
		}

		// add use statement for the super container
		$aliasContainerName = 'ClanCatsContainer' . md5($this->containerName);
		$buffer .= "use " . Container::class . " as " . $aliasContainerName . ";\n\n";

		// generate the the class
		$buffer .= "class $this->containerClassName extends " . $aliasContainerName . " {\n\n";

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
			if (in_array($argumentType, [ServiceArguments::DEPENDENCY, ServiceArguments::PARAMETER]) && $this->invalidServiceBuilderString($argumentValue))
			{
				throw new ContainerBuilderException('Parameter and dependency arguments must be non numeric and not empty to be builded.');
			}

			if ($argumentType === ServiceArguments::DEPENDENCY)
			{
				if ($argumentValue === 'container')
				{
					$buffer = "\$this";
				} 
				// if builder definition exists
				elseif (isset($this->services[$argumentValue])) 
				{
					// if is not shared we can just forward the factory method
					if (!in_array($argumentValue, $this->shared))
					{
						$buffer[] = "\$this->" . 'resolve' . $this->camelizeServiceName($argumentValue) . '()';
					}
					else
					{
						$buffer[] = "\$this->resolvedSharedServices['$argumentValue'] ?? \$this->resolvedSharedServices['$argumentValue'] = \$this->" . 'resolve' . $this->camelizeServiceName($argumentValue) . '()';
					}	
				}
				else
				{
					$buffer[] = "\$this->get('$argumentValue')";
				}
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
			$mappings[] = var_export($serviceName, true) . ' => ' . var_export('resolve' . $this->camelizeServiceName($serviceName), true);
		}

		return "protected \$resolverMethods = [" . implode(', ', $mappings) . "];\n";
	}

	private function generateResolverMethodName($serviceName) : string 
	{
		return 'resolve' . $this->camelizeServiceName($serviceName);
	}

	private function generateResolverMethods() : string
	{
		$buffer = "";

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$buffer .= "protected function resolve" . $this->camelizeServiceName($serviceName) . "() {\n";

			$serviceClassName = $serviceDefinition->getClassName();

			if ($serviceClassName[0] !== "\\")
			{
				$serviceClassName = "\\" . $serviceClassName;
			}

			$buffer .= "\t\$instance = new " . $serviceClassName . "(". $this->generateArgumentsCode($serviceDefinition->getArguments()) .");\n";

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

	private function camelizeServiceName($input) : string
	{
		$input = str_replace([' ', '_'], '.', $input);
	    return str_replace('.', '', ucwords($input, '.'));
	}
}