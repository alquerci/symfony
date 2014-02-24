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
interface Symfony_Component_Form_FormFactoryInterface
{
    /**
     * Returns a form.
     *
     * @see createBuilder()
     *
     * @param string|FormTypeInterface $type    The type of the form
     * @param mixed                    $data    The initial data
     * @param array                    $options The options
     * @param Symfony_Component_Form_FormBuilderInterface     $parent  The parent builder
     *
     * @return Symfony_Component_Form_FormInterface The form named after the type
     *
     * @throws Symfony_Component_Form_Exception_FormException if any given option is not applicable to the given type
     */
    public function create($type = 'form', $data = null, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Returns a form.
     *
     * @see createNamedBuilder()
     *
     * @param string|integer           $name    The name of the form
     * @param string|Symfony_Component_Form_FormTypeInterface $type    The type of the form
     * @param mixed                    $data    The initial data
     * @param array                    $options The options
     * @param Symfony_Component_Form_FormBuilderInterface     $parent  The parent builder
     *
     * @return Symfony_Component_Form_FormInterface The form
     *
     * @throws Symfony_Component_Form_Exception_FormException if any given option is not applicable to the given type
     */
    public function createNamed($name, $type = 'form', $data = null, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Returns a form for a property of a class.
     *
     * @see createBuilderForProperty()
     *
     * @param string               $class    The fully qualified class name
     * @param string               $property The name of the property to guess for
     * @param mixed                $data     The initial data
     * @param array                $options  The options for the builder
     * @param Symfony_Component_Form_FormBuilderInterface $parent   The parent builder
     *
     * @return Symfony_Component_Form_FormInterface The form named after the property
     *
     * @throws Symfony_Component_Form_Exception_FormException if any given option is not applicable to the form type
     */
    public function createForProperty($class, $property, $data = null, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Returns a form builder.
     *
     * @param string|Symfony_Component_Form_FormTypeInterface $type    The type of the form
     * @param mixed                    $data    The initial data
     * @param array                    $options The options
     * @param Symfony_Component_Form_FormBuilderInterface     $parent  The parent builder
     *
     * @return Symfony_Component_Form_FormBuilderInterface The form builder
     *
     * @throws Symfony_Component_Form_Exception_FormException if any given option is not applicable to the given type
     */
    public function createBuilder($type = 'form', $data = null, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Returns a form builder.
     *
     * @param string|integer           $name    The name of the form
     * @param string|Symfony_Component_Form_FormTypeInterface $type    The type of the form
     * @param mixed                    $data    The initial data
     * @param array                    $options The options
     * @param Symfony_Component_Form_FormBuilderInterface     $parent  The parent builder
     *
     * @return Symfony_Component_Form_FormBuilderInterface The form builder
     *
     * @throws Symfony_Component_Form_Exception_FormException if any given option is not applicable to the given type
     */
    public function createNamedBuilder($name, $type = 'form', $data = null, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Returns a form builder for a property of a class.
     *
     * If any of the 'max_length', 'required' and type options can be guessed,
     * and are not provided in the options argument, the guessed value is used.
     *
     * @param string               $class    The fully qualified class name
     * @param string               $property The name of the property to guess for
     * @param mixed                $data     The initial data
     * @param array                $options  The options for the builder
     * @param Symfony_Component_Form_FormBuilderInterface $parent   The parent builder
     *
     * @return Symfony_Component_Form_FormBuilderInterface The form builder named after the property
     *
     * @throws Symfony_Component_Form_Exception_FormException if any given option is not applicable to the form type
     */
    public function createBuilderForProperty($class, $property, $data = null, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);
}
