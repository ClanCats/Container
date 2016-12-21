<?php
namespace ClanCats\Container\Tests\TestServices;

class Car
{
	public $engine;
	public $producer;

	public function __construct(Engine $engine, Producer $producer)
	{
		$this->engine = $engine;
		$this->producer = $producer;
	}
}