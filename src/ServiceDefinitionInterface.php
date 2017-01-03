<?php 
namespace ClanCats\Container;

interface ServiceDefinitionInterface 
{
	/**
	 * Returns the service class name
	 * 
	 * @return string
	 */
	public function getClassName() : string;

	/**
     * Returns the constructor arguments object
     * 
     * @return ServiceFactoryArguments
     */
    public function getArguments() : ServiceFactoryArguments;

    /**
     * Returns all registered method calls
     * 
     * @return array[string => ServiceFactoryArguments]
     */
    public function getMethodCalls() : array;
}	