<?php

use ClanCats\Container\Container as ClanCatsContainer1356c67d7ad1638d816bfb822dd2c25d;

class Foo extends ClanCatsContainer1356c67d7ad1638d816bfb822dd2c25d {

protected $serviceResolverType = ['producer' => 0, 'car' => 0, 'engine' => 0];

protected $resolverMethods = ['producer' => 'resolveProducer', 'car' => 'resolveCar', 'engine' => 'resolveEngine'];

protected function resolveProducer() {
	$instance = new \ClanCats\Container\Tests\TestServices\Producer('BMW');
	$this->resolvedSharedServices['producer'] = $instance;
	return $instance;
}
protected function resolveCar() {
	$instance = new \ClanCats\Container\Tests\TestServices\Car($this->resolvedSharedServices['engine'] ?? $this->resolvedSharedServices['engine'] = $this->resolveEngine(), $this->resolvedSharedServices['producer'] ?? $this->resolvedSharedServices['producer'] = $this->resolveProducer());
	$this->resolvedSharedServices['car'] = $instance;
	return $instance;
}
protected function resolveEngine() {
	$instance = new \ClanCats\Container\Tests\TestServices\Engine();
	$instance->setPower(122);
	$this->resolvedSharedServices['engine'] = $instance;
	return $instance;
}


}