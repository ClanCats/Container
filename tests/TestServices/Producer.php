<?php
namespace ClanCats\Container\Tests\TestServices;

class Producer
{
    public $name;

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }
}