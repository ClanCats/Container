<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2023 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
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
     * @return ServiceArguments
     */
    public function getArguments() : ServiceArguments;

    /**
     * Returns all registered method calls
     * 
     * @return array<array{string, ServiceArguments}>
     */
    public function getMethodCalls() : array;

    /**
     * Get all metadata of the service definition
     *
     * @return array<string, array<mixed>>
     */
    public function getMetaData() : array;
}   
