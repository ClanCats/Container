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
	 * An array of converted service names
	 * The normalized service names is camel cased and should be usable as method name.
	 * 
	 * @param array[string]
	 */
	private $normalizedServiceNames = [];

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
		if (empty($containerName) || !(preg_match('/^[a-zA-Z0-9\\\\_]*$/', $containerName)) || is_numeric($containerName[0]))
		{
			throw new ContainerBuilderException('The container name cannot be empty, start with a number or contain sepcial characters except "\\".');
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
			throw new ContainerBuilderException('The "'.$serviceName.'" servicename must be a string, cannot be numeric, empty or contain any special characters except "." and "_".');
		}

		// add the service definition
		$this->services[$serviceName] = $serviceDefinition;

		// generate the normalized name
		$this->generateNormalizedServiceName($serviceName);

		// set the shared unshared flag
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

		// also check the first character the string contains with a number
		if (is_numeric($value[0]) || $value[0] === '.' || $value[0] === '_') {
			return true;
		}

		$lastCharacter = $value[strlen($value) - 1];
		if ($lastCharacter === '.' || $lastCharacter === '_') {
			return true;
		}

		return false;
	}

	/**
	 * Generate a camelized service name
	 * 
	 * @param string 			$serviceName
	 * @return string
	 */
	private function camelizeServiceName(string $serviceName) : string
	{
	    return str_replace(['.', '_'], '', ucwords(str_replace(['.', '_'], '.', $serviceName), '.'));
	}

	/**
	 * Generates the "normalizedServiceNames" array.
	 * 
	 * @param string 			$serviceName
	 * @return void 
	 */
	private function generateNormalizedServiceName(string $serviceName) : void
	{
		$normalizedServiceName = $this->camelizeServiceName($serviceName);

		$duplicateCounter = 0;
		$countedNormalizedServiceName = $normalizedServiceName;
		while(in_array($countedNormalizedServiceName, $this->normalizedServiceNames))
		{
			$duplicateCounter++;
			$countedNormalizedServiceName = $normalizedServiceName . $duplicateCounter;
		}

		$this->normalizedServiceNames[$serviceName] = $countedNormalizedServiceName;
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
		$buffer .= "class $this->containerClassName extends $aliasContainerName {\n\n";

		$buffer .= $this->generateResolverTypes() . "\n";
		$buffer .= $this->generateResolverMappings() . "\n";
		$buffer .= $this->generateResolverMethods() . "\n";

		return $buffer . "\n}";
	}

	/**
	 * Generate the service resolver method name for the given service
	 * 
	 * @param string 			$serviceName
	 * @return string
	 */
	private function getResolverMethodName(string $serviceName) : string 
	{
		if (!isset($this->normalizedServiceNames[$serviceName]))
		{
			throw new ContainerBuilderException("The '" . $serviceName . "' service has never been definied.");
		}

		return 'resolve' . $this->normalizedServiceNames[$serviceName];
	}

	/**
	 * Generate arguments code 
	 * 
	 * @param ServiceArguments 			$arguments
	 * @return string
	 */
	private function generateArgumentsCode(ServiceArguments $arguments) : string
	{
		$buffer = [];

		foreach($arguments->getAll() as list($argumentValue, $argumentType))
		{
			if ($argumentType === ServiceArguments::DEPENDENCY)
			{
				if ($argumentValue === 'container')
				{
					$buffer[] = "\$this";
				}
				// if the dependency is defined in the current container builder
				// we can be sure that it exists and directly call the resolver method
				elseif (isset($this->services[$argumentValue])) 
				{
					$resolverMethodCall = "\$this->" . $this->getResolverMethodName($argumentValue) . '()';

					// if is not shared we can just forward the factory method
					if (!in_array($argumentValue, $this->shared))
					{
						$buffer[] = $resolverMethodCall;
					}
					// otherwise we have to check if the singleton has 
					// already been resolved.
					else
					{
						$buffer[] = "\$this->resolvedSharedServices['$argumentValue'] ?? \$this->resolvedSharedServices['$argumentValue'] = " . $resolverMethodCall;
					}	
				}
				// if the dependency is not defined inside the container builder
				// it might be added dynamically later. So we just access the containers `get` method.
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

	/**
	 * Generate the resolver types array 
	 * 
	 * @return string 
	 */
	private function generateResolverTypes() : string
	{
		$types = []; 

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$types[] = var_export($serviceName, true) . ' => ' . Container::RESOLVE_METHOD;
		}

		return "protected \$serviceResolverType = [" . implode(', ', $types) . "];\n";
	}

	/**
	 * Generate the resolver mappings array
	 * 
	 * @return string 
	 */
	private function generateResolverMappings() : string
	{
		$mappings = []; 

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$mappings[] = var_export($serviceName, true) . ' => ' . var_export($this->getResolverMethodName($serviceName), true);
		}

		return "protected \$resolverMethods = [" . implode(', ', $mappings) . "];\n";
	}

	/**
	 * Generate the resolver methods
	 * 
	 * @return string
	 */
	private function generateResolverMethods() : string
	{
		$buffer = "";

		foreach($this->services as $serviceName => $serviceDefinition)
		{
			$buffer .= "protected function " . $this->getResolverMethodName($serviceName) . "() {\n";

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
}