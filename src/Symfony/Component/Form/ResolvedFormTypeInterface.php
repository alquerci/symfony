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
 * A wrapper for a form type and its extensions.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_Form_ResolvedFormTypeInterface
{
    /**
     * Returns the name of the type.
     *
     * @return string The type name.
     */
    public function getName();

    /**
     * Returns the parent type.
     *
     * @return Symfony_Component_Form_ResolvedFormTypeInterface The parent type or null.
     */
    public function getParent();

    /**
     * Returns the wrapped form type.
     *
     * @return Symfony_Component_Form_FormTypeInterface The wrapped form type.
     */
    public function getInnerType();

    /**
     * Returns the extensions of the wrapped form type.
     *
     * @return Symfony_Component_Form_FormTypeExtensionInterface[] An array of {@link FormTypeExtensionInterface} instances.
     */
    public function getTypeExtensions();

    /**
     * Creates a new form builder for this type.
     *
     * @param Symfony_Component_Form_FormFactoryInterface $factory The form factory.
     * @param string               $name    The name for the builder.
     * @param array                $options The builder options.
     * @param Symfony_Component_Form_FormBuilderInterface $parent  The parent builder object or null.
     *
     * @return Symfony_Component_Form_FormBuilderInterface The created form builder.
     */
    public function createBuilder(Symfony_Component_Form_FormFactoryInterface $factory, $name, array $options = array(), Symfony_Component_Form_FormBuilderInterface $parent = null);

    /**
     * Creates a new form view for a form of this type.
     *
     * @param Symfony_Component_Form_FormInterface     $form   The form to create a view for.
     * @param Symfony_Component_Form_FormView $parent The parent view or null.
     *
     * @return Symfony_Component_Form_FormView The created form view.
     */
    public function createView(Symfony_Component_Form_FormInterface $form, Symfony_Component_Form_FormView $parent = null);

    /**
     * Configures a form builder for the type hierarchy.
     *
     * @param Symfony_Component_Form_FormBuilderInterface $builder The builder to configure.
     * @param array                $options The options used for the configuration.
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options);

    /**
     * Configures a form view for the type hierarchy.
     *
     * It is called before the children of the view are built.
     *
     * @param Symfony_Component_Form_FormView      $view    The form view to configure.
     * @param Symfony_Component_Form_FormInterface $form    The form corresponding to the view.
     * @param array         $options The options used for the configuration.
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options);

    /**
     * Finishes a form view for the type hierarchy.
     *
     * It is called after the children of the view have been built.
     *
     * @param Symfony_Component_Form_FormView      $view    The form view to configure.
     * @param Symfony_Component_Form_FormInterface $form    The form corresponding to the view.
     * @param array         $options The options used for the configuration.
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options);

    /**
     * Returns the configured options resolver used for this type.
     *
     * @return Symfony_Component_OptionsResolver_OptionsResolverInterface The options resolver.
     */
    public function getOptionsResolver();
}
