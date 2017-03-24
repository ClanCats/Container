<?php 
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