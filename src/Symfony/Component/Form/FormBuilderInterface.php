<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Form_FormBuilderInterface extends Traversable, Countable, Symfony_Component_Form_FormConfigBuilderInterface
{
    /**
     * Adds a new field to this group. A field must have a unique name within
     * the group. Otherwise the existing field is overwritten.
     *
     * If you add a nested group, this group should also be represented in the
     * object hierarchy.
     *
     * @param string|integer|Symfony_Component_Form_FormBuilderInterface $child
     * @param string|Symfony_Component_Form_FormTypeInterface            $type
     * @param array                               $options
     *
     * @return Symfony_Component_Form_FormBuilderInterface The builder object.
     */
    public function add($child, $type = null, array $options = array());

    /**
     * Creates a form builder.
     *
     * @param string                   $name    The name of the form or the name of the property
     * @param string|Symfony_Component_Form_FormTypeInterface $type    The type of the form or null if name is a property
     * @param array                    $options The options
     *
     * @return Symfony_Component_Form_FormBuilderInterface The created builder.
     */
    public function create($name, $type = null, array $options = array());

    /**
     * Returns a child by name.
     *
     * @param string $name The name of the child
     *
     * @return Symfony_Component_Form_FormBuilderInterface The builder for the child
     *
     * @throws Symfony_Component_Form_Exception_FormException if the given child does not exist
     */
    public function get($name);

    /**
     * Removes the field with the given name.
     *
     * @param string $name
     *
     * @return Symfony_Component_Form_FormBuilderInterface The builder object.
     */
    public function remove($name);

    /**
     * Returns whether a field with the given name exists.
     *
     * @param string $name
     *
     * @return Boolean
     */
    public function has($name);

    /**
     * Returns the children.
     *
     * @return array
     */
    public function all();

    /**
     * Creates the form.
     *
     * @return Form The form
     */
    public function getForm();

    /**
     * Sets the parent builder.
     *
     * @param Symfony_Component_Form_FormBuilderInterface $parent The parent builder
     *
     * @return Symfony_Component_Form_FormBuilderInterface The builder object.
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3. You
     *             should not rely on the parent of a builder, because it is
     *             likely that the parent is only set after turning the builder
     *             into a form.
     */
    public function setParent(Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Returns the parent builder.
     *
     * @return Symfony_Component_Form_FormBuilderInterface The parent builder
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3. You
     *             should not rely on the parent of a builder, because it is
     *             likely that the parent is only set after turning the builder
     *             into a form.
     */
    public function getParent();

    /**
     * Returns whether the builder has a parent.
     *
     * @return Boolean
     *
     * @deprecated Deprecated since version 2.2, to be removed in 2.3. You
     *             should not rely on the parent of a builder, because it is
     *             likely that the parent is only set after turning the builder
     *             into a form.
     */
    public function hasParent();
}
