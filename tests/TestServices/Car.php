<?php
namespace ClanCats\Container\Tests\TestServices;

class Car
{
	public $engine;
	public $producer;

	public function __construct(Engine $engine, Producer $producer = null)
	{
		$this->engine = $engine;
		$this->producer = $producer;
	}

	public function setProducer(Producer $producer)
	{
		$this->producer = $producer;
	}
}