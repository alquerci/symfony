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
class Symfony_Component_Form_Extension_Validator_Type_RepeatedTypeValidatorExtension extends Symfony_Component_Form_AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        // Map errors to the first field
        $errorMapping = function (Symfony_Component_OptionsResolver_Options $options) {
            return array('.' => $options['first_name']);
        };

        $resolver->setDefaults(array(
            'error_mapping' => $errorMapping,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'repeated';
    }
}
