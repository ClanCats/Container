<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2026 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container\ContainerParser\Nodes;

/**
 * A placeholder for a generic type parameter inside a parsed generic template.
 *
 * The parser emits this node wherever a declared type parameter is used inside a
 * generic body — as a bare identifier in an argument position, wrapped in a
 * `::class` constant, or (via the class name placeholder on
 * `ServiceDefinitionNode`) as the service class name. When a generic is
 * materialized the `GenericTemplateExpander` replaces each placeholder with a
 * concrete node built from the supplied type argument.
 */
class TypeParameterPlaceholderNode extends BaseNode implements AssignableNode
{
    /**
     * The type parameter name this placeholder refers to
     *
     * @var string
     */
    protected string $name;

    /**
     * Whether the placeholder originated from a `T::class` constant. Class name
     * type arguments substituted into such a placeholder keep the `::class`
     * wrapping, everything else is substituted verbatim.
     *
     * @var bool
     */
    protected bool $asClassConstant;

    /**
     * Placeholder constructor
     *
     * @param string        $name
     * @param bool          $asClassConstant
     */
    public function __construct(string $name, bool $asClassConstant = false)
    {
        $this->name = $name;
        $this->asClassConstant = $asClassConstant;
    }

    /**
     * Get the referenced type parameter name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get if the placeholder originated from a `T::class` constant
     */
    public function isClassConstant() : bool
    {
        return $this->asClassConstant;
    }
}
