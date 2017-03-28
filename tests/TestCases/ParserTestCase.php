<?php
namespace ClanCats\Container\Tests\TestCases;

use ClanCats\Container\ContainerParser\{
	ContainerParser
};

abstract class ParserTestCase extends LexerTestCase
{
	protected function parserFromCode(string $parserClassName, string $code) : ContainerParser
	{
		return new $parserClassName($this->tokensFromCode($code));
	}
}