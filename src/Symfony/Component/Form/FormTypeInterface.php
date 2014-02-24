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
interface Symfony_Component_Form_FormTypeInterface
{
    /**
     * Builds the form.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the form.
     *
     * @see Symfony_Component_Form_FormTypeExtensionInterface::buildForm()
     *
     * @param Symfony_Component_Form_FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options);

    /**
     * Builds the form view.
     *
     * This method is called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the view.
     *
     * A view of a form is built before the views of the child forms are built.
     * This means that you cannot access child views in this method. If you need
     * to do so, move your logic to {@link finishView()} instead.
     *
     * @see Symfony_Component_Form_FormTypeExtensionInterface::buildView()
     *
     * @param Symfony_Component_Form_FormView $view    The view
     * @param Symfony_Component_Form_FormInterface     $form    The form
     * @param array             $options The options
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options);

    /**
     * Finishes the form view.
     *
     * This method gets called for each type in the hierarchy starting form the
     * top most type. Type extensions can further modify the view.
     *
     * When this method is called, views of the form's children have already
     * been built and finished and can be accessed. You should only implement
     * such logic in this method that actually accesses child views. For everything
     * else you are recommended to implement {@link buildView()} instead.
     *
     * @see Symfony_Component_Form_FormTypeExtensionInterface::finishView()
     *
     * @param Symfony_Component_Form_FormView $view    The view
     * @param Symfony_Component_Form_FormInterface     $form    The form
     * @param array             $options The options
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options);

    /**
     * Sets the default options for this type.
     *
     * @param Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver The resolver for the options.
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver);

    /**
     * Returns the name of the parent type.
     *
     * You can also return a type instance from this method, although doing so
     * is discouraged because it leads to a performance penalty. The support
     * for returning type instances may be dropped from future releases.
     *
     * @return string|null|Symfony_Component_Form_FormTypeInterface The name of the parent type if any, null otherwise.
     */
    public function getParent();

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName();
}
