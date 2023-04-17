<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2023 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

interface ServiceProviderClassLookupInterface 
{
    /**
     * Returns the class name for a given service
     * 
     * @param string                    $serviceName
     * @param Container                 $container
     * @return class-string
     */
    public function lookupClassName(string $serviceName, Container $container) : string;
}   
