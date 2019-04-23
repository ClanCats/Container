<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2019 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

class ServiceFactory extends ServiceDefinition implements ServiceFactoryInterface
{
    /**
     * Construct your object, or value based on the given container.
     * 
     * @param Container             $container
     * @return mixed
     */
    public function create(Container $container)
    {
        $instance = new $this->className(...$this->constructorArguments->resolve($container)); 

        foreach($this->methodCallers as list($method, $arguments))
        {
            $instance->{$method}(... $arguments->resolve($container));
        }

        return $instance;
    }
}   
