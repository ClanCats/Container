# Container

Solid Service Container with fast and cachable dependency injection.

[![Build Status](https://travis-ci.org/ClanCats/Container.svg?branch=master)](https://travis-ci.org/ClanCats/Container)
[![Packagist](https://img.shields.io/packagist/dt/clancats/container.svg)](https://packagist.org/packages/clancats/container)
[![Packagist](https://img.shields.io/packagist/l/clancats/container.svg)]()
[![GitHub release](https://img.shields.io/github/release/clancats/container.svg)](https://github.com/ClanCats/Container/releases)

_Requires PHP >= 7.1_


### Service Provider

A service pro

```php
class SessionServiceProdiver implements ServiceProviderInterface
{
	public function provides() : array
	{
		return 
		[
			'session.storage.mysql' => [
				'class' => 'Session\\Storage\\MySQL',
				'arguments' => ['@mysql']
			],

			// serviceName => [methodName, isShared]
			'session' => ['provideSession', true],
		];
	}

	public function provideSession(Container $c)
	{
		return new \Session\Manager($c->);
	}
}
```