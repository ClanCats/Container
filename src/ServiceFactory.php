<?php 
namespace ClanCats\Container;

class ServiceFactory implements ServiceFactoryInterface 
{
	/**
	 * Static instance constructor to allow eye candy like:
	 * 
	 *     ServiceFactory::for('\Acme\SessionService')
	 * 	      ->addDependencyArgument('storage')
	 * 		  ->addParameterArgument('session_token')
	 * 		  ->addScalarArgument(600)
	 *        ->add
	 * 
	 * Or the shorter way
	 */
	public static function for(string $serviceClassName)
	{
		return new static($serviceClassName);
	}

	/**
	 * The services class name
	 */
	protected $className;

	/**
	 * The consturcor arguments of the service
	 * 
	 * @var ServiceFactoryArguments
	 */
	protected $constructorArguments;

	/**
	 * An array of method calls after service construction
	 * 
	 * @var array[string => ServiceFactoryArguments]
	 */
	protected $methodCallers = [];

	/**
	 * Construct a new service loader with a given cache directory
	 * 
	 * @param string 				$cacheDirectory
	 * @return void
	 */
	public function __construct(string $className, array $arguments = [])
	{
		$this->className = $className;
		$this->constructorArguments = ServiceFactoryArguments::fromArray($arguments);
	}

	/**
	 * Generates an array of arguments 
	 */
	private function generateConstructorArguments(Container $container) : array
	{

	}

	/**
	 * Construct your object, or value based on the given container.
	 * 
	 * @param Container 			$container
	 * @return mixed
	 */
	public function create(Container $container)
	{
		return new $this->className(...$this->generateConstructorArguments($container)); 
	}
}	