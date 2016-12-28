<?php 
namespace ClanCats\Container;

class ServiceFactoryArguments 
{
    /**
     * Static instance constructor from array for eye candy
     * 
     *     ServiceFactoryArguments::fromArray([
     *         '@session.storage.redis',
     *         ':session_token',
     *         600, // session lifetime
     *     ])
     */
    public static function fromArray(array $arguments) : ServiceFactoryArguments
    {
        $arguments = new static:
        $arguments->addArgumentsFromArray($arguments);
        return $arguments;
    }

    /**
     * Available service factory argument types
     * Subclasses would be cleaner, but this could have 
     * a real performance impact so lets do it oldschool 
     */ 
    const RAW = 0;
    const PARAMETER = 1;
    const DEPENDENCY = 2;

    /**
     * An array of arguments
     * 
     * @var array[[string, int]]
     */
    protected $arguments = [];

    /**
     * Add an service argument of type
     * 
     * @param mixed             $argumentValue
     * @param int               $argumentType
     * @return self
     */
    public function addArgument($argumentValue, int $argumentType) : ServiceFactoryArguments
    {
        $this->arguments[] = [$argumentValue, $argumentType]; return $this;
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addRawArgument($argumentValue) : ServiceFactoryArguments
    {
        return $this->addRawArgument($argumentValue, static::RAW);
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addDependencyArgument($argumentValue) : ServiceFactoryArguments
    {
        return $this->addRawArgument($argumentValue, static::DEPENDENCY);
    }

    /**
     * Add a simply raw argument,
     * 
     * @param mixed             $argumentValue
     * @return self
     */
    public function addParameterArgument($argumentValue) : ServiceFactoryArguments
    {
        return $this->addRawArgument($argumentValue, static::PARAMETER);
    }

    /**
     * Add arguments with a simple array
     * 
     *  - @ prefix indicates dependency
     *  - : prefix indicates parameter
     * 
     * @param array                 $argumentsArray
     * @return void
     */
    public function addArgumentsFromArray(array $argumentsArray) : void
    {
        foreach($argumentsArray as $argument)
        {
            if (is_string($argument) && ($argument[0] === '@' || $argument[0] === ':')))
            {
                if ($argument[0] === '@') {
                    $this->addDependencyArgument(substr($argument, 1));
                } elseif ($argument[0] === ':') {
                    $this->addParameterArgument(substr($argument, 1));
                }
            } else  {
                $this->addRawArgument($argument);
            }
        }
    }
}   