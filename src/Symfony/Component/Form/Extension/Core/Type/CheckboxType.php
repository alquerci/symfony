<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_CheckboxType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new Symfony_Component_Form_Extension_Core_DataTransformer_BooleanToStringTransformer($options['value']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'value'   => $options['value'],
            'checked' => null !== $form->getViewData(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $emptyData = function (Symfony_Component_Form_FormInterface $form, $clientData) {
            return $clientData;
        };

        $resolver->setDefaults(array(
            'value'      => '1',
            'empty_data' => $emptyData,
            'compound'   => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'checkbox';
    }
}
