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
abstract class Symfony_Component_Form_AbstractTypeExtension implements Symfony_Component_Form_FormTypeExtensionInterface
{
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
            trigger_error('getDefaultOptions() is deprecated since version 2.1 and will be removed in 2.3. Use setDefaultOptions() instead.', E_USER_DEPRECATED);

            $resolver->setDefaults($defaults);
        }

        if (!empty($allowedTypes)) {
            trigger_error('getAllowedOptionValues() is deprecated since version 2.1 and will be removed in 2.3. Use setDefaultOptions() instead.', E_USER_DEPRECATED);

            $resolver->addAllowedValues($allowedTypes);
        }
    }

    /**
     * Overrides the default options form the extended type.
     *
     * @return array
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     *             Use {@link setDefaultOptions()} instead.
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * Returns the allowed option values for each option (if any).
     *
     * @return array The allowed option values
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     *             Use {@link setDefaultOptions()} instead.
     */
    public function getAllowedOptionValues()
    {
        return array();
    }
}
