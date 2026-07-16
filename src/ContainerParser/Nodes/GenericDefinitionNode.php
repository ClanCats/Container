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
 * A generic service definition template.
 *
 * A generic captures a parsed `ServiceDefinitionNode` template (everything from
 * the assign onwards) together with the names of its type parameters. Every use
 * of a type parameter inside the template is represented by a
 * `TypeParameterPlaceholderNode`. It is never turned into a service itself;
 * instead a `GenericMaterializationNode` deep clones the template and
 * substitutes concrete nodes for the placeholders (see
 * `GenericTemplateExpander`). Generics are therefore a pure compile time
 * construct and leave no trace in the generated container.
 */
class GenericDefinitionNode extends BaseNode
{
    /**
     * The generic name
     *
     * @var string
     */
    protected string $name;

    /**
     * The ordered type parameter names
     *
     * @var array<string>
     */
    protected array $typeParameters = [];

    /**
     * The parsed service definition template. Uses of a type parameter are
     * represented by `TypeParameterPlaceholderNode`s inside this tree.
     *
     * @var ServiceDefinitionNode|null
     */
    protected ?ServiceDefinitionNode $template = null;

    /**
     * Does this definition override an existing generic?
     *
     * @var bool
     */
    protected bool $isOverride = false;

    /**
     * Get the generics name
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set the generics name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * Add a type parameter name
     */
    public function addTypeParameter(string $name) : void
    {
        $this->typeParameters[] = $name;
    }

    /**
     * Get all type parameter names in order
     *
     * @return array<string>
     */
    public function getTypeParameters() : array
    {
        return $this->typeParameters;
    }

    /**
     * Set the parsed service definition template
     *
     * @param ServiceDefinitionNode     $template
     */
    public function setTemplate(ServiceDefinitionNode $template) : void
    {
        $this->template = $template;
    }

    /**
     * Get the parsed service definition template
     *
     * @return ServiceDefinitionNode|null
     */
    public function getTemplate() : ?ServiceDefinitionNode
    {
        return $this->template;
    }

    /**
     * Get if this definition overrides an existing generic
     */
    public function isOverride() : bool
    {
        return $this->isOverride;
    }

    /**
     * Set if this definition overrides an existing generic
     */
    public function setIsOverride(bool $isOverride) : void
    {
        $this->isOverride = $isOverride;
    }
}
