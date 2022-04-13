<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2022 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

use ClanCats\Container\{
    Exceptions\UnknownServiceException,
    Exceptions\InvalidServiceException
};


class ServiceProviderArray implements ServiceProviderInterface 
{
    /**
     * Array of to be provided services 
     * You can subclass the ServiceProviderArray and simply overwrite 
     * this attribute. You can also create an instance of the array provider and 
     * set your services using the `setServices() method.
     * 
     * @var array
     */
    protected $services = [];

    /**
     * Set the providers services 
     * 
     * @param array             $services
     */
    public function setServices(array $services) 
    {
        $this->services = $services;
    }

    /**
     * What services are provided by the service provider
     * 
     * @return array[string]
     */
    public function provides() : array
    {
        return array_keys($this->services);
    }

    /**
     * Resolve a service with the given name 
     * 
     * @param string                    $serviceName
     * @param Container                 $container
     * @return array[mixed, bool]
     */
    public function resolve(string $serviceName, Container $container) : array
    {
        if (!isset($this->services[$serviceName]))
        {
            throw new UnknownServiceException('The service provider "' . get_class($this) . '" does not support service resolving of the service "' . $serviceName . '"');
        }

        $serviceConfiguration = $this->services[$serviceName];

        // create the factory 
        $factory = ServiceFactory::fromArray($serviceConfiguration);

        // resolve the service 
        $service = $factory->create($container);

        // if the service is not shared skip the store
        if ((!isset($serviceConfiguration['shared'])) || $serviceConfiguration['shared'] !== false)
        {
            return [$service, true];
        }

        return [$service, false];
    }
}   
