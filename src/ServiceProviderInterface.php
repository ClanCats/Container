<?php 
namespace ClanCats\Container;

interface ServiceProviderInterface 
{
	/**
	 * What services are provided by the service provider
	 * 
	 * @return array[string => string]
	 */
	public function provides() : array;
}	