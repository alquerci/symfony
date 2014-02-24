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
interface Symfony_Component_Form_FormTypeExtensionInterface
{
    /**
     * Builds the form.
     *
     * This method is called after the extended type has built the form to
     * further modify it.
     *
     * @see Symfony_Component_Form_FormTypeInterface::buildForm()
     *
     * @param Symfony_Component_Form_FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options);

    /**
     * Builds the view.
     *
     * This method is called after the extended type has built the view to
     * further modify it.
     *
     * @see Symfony_Component_Form_FormTypeInterface::buildView()
     *
     * @param Symfony_Component_Form_FormView $view    The view
     * @param Symfony_Component_Form_FormInterface     $form    The form
     * @param array             $options The options
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options);

    /**
     * Finishes the view.
     *
     * This method is called after the extended type has finished the view to
     * further modify it.
     *
     * @see Symfony_Component_Form_FormTypeInterface::finishView()
     *
     * @param Symfony_Component_Form_FormView $view    The view
     * @param Symfony_Component_Form_FormInterface     $form    The form
     * @param array             $options The options
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options);

    /**
     * Overrides the default options from the extended type.
     *
     * @param Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver);

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType();
}
