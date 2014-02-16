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
abstract class Symfony_Component_Form_AbstractType implements Symfony_Component_Form_FormTypeInterface
{
    /**
     * @var array
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    private $extensions = array();

    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $defaults = $this->getDefaultOptions(array());
        $allowedTypes = $this->getAllowedOptionValues(array());

        if (!empty($defaults)) {
            version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getDefaultOptions() is deprecated since version 2.1 and will be removed in 2.3. Use setDefaultOptions() instead.', E_USER_DEPRECATED);

            $resolver->setDefaults($defaults);
        }

        if (!empty($allowedTypes)) {
            version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getAllowedOptionValues() is deprecated since version 2.1 and will be removed in 2.3. Use setDefaultOptions() instead.', E_USER_DEPRECATED);

            $resolver->addAllowedValues($allowedTypes);
        }
    }

    /**
     * Returns the default options for this type.
     *
     * @param array $options Unsupported as of Symfony 2.1.
     *
     * @return array The default options
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     *             Use {@link setDefaultOptions()} instead.
     */
    public function getDefaultOptions(array $options)
    {
        return array();
    }

    /**
     * Returns the allowed option values for each option (if any).
     *
     * @param array $options Unsupported as of Symfony 2.1.
     *
     * @return array The allowed option values
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     *             Use {@link setDefaultOptions()} instead.
     */
    public function getAllowedOptionValues(array $options)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * Sets the extensions for this type.
     *
     * @param Symfony_Component_Form_FormTypeExtensionInterface[] $extensions An array of Symfony_Component_Form_FormTypeExtensionInterface
     *
     * @throws Symfony_Component_Form_Exception_UnexpectedTypeException if any extension does not implement Symfony_Component_Form_FormTypeExtensionInterface
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    public function setExtensions(array $extensions)
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('setExtensions() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);

        $this->extensions = $extensions;
    }

    /**
     * Returns the extensions associated with this type.
     *
     * @return Symfony_Component_Form_FormTypeExtensionInterface[] An array of Symfony_Component_Form_FormTypeExtensionInterface
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Form_ResolvedFormTypeInterface::getTypeExtensions()} instead.
     */
    public function getExtensions()
    {
        version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('getExtensions() is deprecated since version 2.1 and will be removed in 2.3. Use Symfony_Component_Form_ResolvedFormTypeInterface::getTypeExtensions instead.', E_USER_DEPRECATED);

        return $this->extensions;
    }
}
