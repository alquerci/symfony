<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_FileType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            'type'  => 'file',
            'value' => '',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(Symfony_Component_Form_FormView $view, Symfony_Component_Form_FormInterface $form, array $options)
    {
        $view
            ->vars['multipart'] = true
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound' => false,
            'data_class' => 'Symfony_Component_HttpFoundation_File_File',
            'empty_data' => null,
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
        return 'file';
    }
}
