<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_RepeatedType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        // Overwrite required option for child fields
        $options['first_options']['required']  = $options['required'];
        $options['second_options']['required'] = $options['required'];

        if (!isset($options['options']['error_bubbling'])) {
            $options['options']['error_bubbling'] = $options['error_bubbling'];
        }

        $builder
            ->addViewTransformer(new Symfony_Component_Form_Extension_Core_DataTransformer_ValueToDuplicatesTransformer(array(
                $options['first_name'],
                $options['second_name'],
            )))
            ->add($options['first_name'], $options['type'], array_merge($options['options'], $options['first_options']))
            ->add($options['second_name'], $options['type'], array_merge($options['options'], $options['second_options']))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type'           => 'text',
            'options'        => array(),
            'first_options'  => array(),
            'second_options' => array(),
            'first_name'     => 'first',
            'second_name'    => 'second',
            'error_bubbling' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'repeated';
    }
}
