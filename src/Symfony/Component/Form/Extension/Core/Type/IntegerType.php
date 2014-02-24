<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Extension_Core_Type_IntegerType extends Symfony_Component_Form_AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new Symfony_Component_Form_Extension_Core_DataTransformer_IntegerToLocalizedStringTransformer(
                $options['precision'],
                $options['grouping'],
                $options['rounding_mode']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            // default precision is locale specific (usually around 3)
            'precision'     => null,
            'grouping'      => false,
            // Integer cast rounds towards 0, so do the same when displaying fractions
            'rounding_mode' => NumberFormatter::ROUND_DOWN,
            'compound'      => false,
        ));

        $resolver->setAllowedValues(array(
            'rounding_mode' => array(
                NumberFormatter::ROUND_FLOOR,
                NumberFormatter::ROUND_DOWN,
                NumberFormatter::ROUND_HALFDOWN,
                NumberFormatter::ROUND_HALFEVEN,
                NumberFormatter::ROUND_HALFUP,
                NumberFormatter::ROUND_UP,
                NumberFormatter::ROUND_CEILING,
            ),
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
        return 'integer';
    }
}
