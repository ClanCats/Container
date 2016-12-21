<?php 

require "vendor/autoload.php";

$loader = ClanCats\Container\ServiceLoader(__DIR__ . "/cache/");

$loader->register([

	'test' => 'Test\\Database\\Manager',

	'session.provier.db' => 'Test\\Session\\ProviderDB(@db, @config(session.))'
	'session.provier.redis' => 'Test\\Session\\ProviderRedis(@redis.client)',

	'session' => 'Test\\Session\\Manager(@config(session), @session.provider.redis)',
	'session.cart' => 'Test\\Session\\Manager(@config(session.cart), @session.provider.db)'

	'db' => [
		'class' => 'Test\\Database\\Manager',
		'arguments' => ['@connection', 'foo'] 
	],
	
]);

$container = Container($loader);

class CachedContainer extends Container
{
	protected $parameters = [
		'some' => 'cached',
	];

	const RESOLVE_METHOD = 0;
	const RESOLVE_FACTORY = 1;

	protected $serviceResolverType = [
		'config' => static::RESOLVE_METHOD,
		'config.source.php' => static::RESOLVE_METHOD,
		'orbit' => static::RESOLVE_METHOD,
		'some' => static::RESOLVE_FACTORY,
	];

	protected $resolverMethods = [
		'config' => 'resolveConfig',
		'config.source.php' => 'resolveConfigSorucePhp',
		'orbit' => 'resolveOrbit',
	];

	protected $resolverFactories = [
		'some' => [$blaClass, 'methodName']
	]

	private $__resolverConfigInstances = [];
	public function resolveConfig($name)
	{
		if (!isset($__resolverConfigInstances[$name]))
		{
			$__resolverConfigInstances[$name] = new Config($name, $this->resolveOrbit(), $this->resolveConfigSourcePhp());
		}

		return $__resolverConfigInstances[$name];
	}
}