<?php
namespace ClanCats\Container\Tests\TestServices;

class Engine
{
    public $power = 0;

    public function setPower(int $power)
    {
        $this->power = $power;
    }
}