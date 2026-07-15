<?php
namespace ClanCats\Container\Tests\TestServices;

use ClanCats\Container\Container;

/**
 * A container using method resolvers with return types that cannot be resolved
 * to a class name, used to exercise the reflection based `getClassForService`.
 */
class ClassNameLookupContainer extends Container
{
    protected array $resolverMethods = [
        'builtin' => 'resolveBuiltin',
        'notype' => 'resolveNoType',
        'engine' => 'resolveEngine',
    ];

    protected array $serviceResolverType = [
        'builtin' => self::RESOLVE_METHOD,
        'notype' => self::RESOLVE_METHOD,
        'engine' => self::RESOLVE_METHOD,
    ];

    public function __construct(array $initalParameters = [])
    {
        parent::__construct();
    }

    // a builtin return type cannot be resolved to a class name
    protected function resolveBuiltin() : string
    {
        return 'hello';
    }

    // a missing return type cannot be resolved to a class name
    protected function resolveNoType()
    {
        return new Engine();
    }

    // a proper class return type can be resolved
    protected function resolveEngine() : Engine
    {
        return new Engine();
    }
}
