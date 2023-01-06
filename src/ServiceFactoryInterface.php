<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2023 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

interface ServiceFactoryInterface 
{
    /**
     * Construct your object, or value based on the given container.
     * 
     * @param Container             $container
     * @return mixed
     */
    public function create(Container $container);
}   
