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
 * Extension supporting the Symfony2 Validator component in forms.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Validator_ValidatorExtension extends Symfony_Component_Form_AbstractExtension
{
    private $validator;

    public function __construct(Symfony_Component_Validator_ValidatorInterface $validator)
    {
        $this->validator = $validator;

        // Register the form constraints in the validator programmatically.
        // This functionality is required when using the Form component without
        // the DIC, where the XML file is loaded automatically. Thus the following
        // code must be kept synchronized with validation.xml

        /** @var Symfony_Component_Validator_Mapping_ClassMetadata $metadata */
        $metadata = $this->validator->getMetadataFactory()->getMetadataFor('Symfony_Component_Form_Form');
        $metadata->addConstraint(new Symfony_Component_Form_Extension_Validator_Constraints_Form());
        $metadata->addPropertyConstraint('children', new Symfony_Component_Validator_Constraints_Valid());
    }

    public function loadTypeGuesser()
    {
        return new Symfony_Component_Form_Extension_Validator_ValidatorTypeGuesser($this->validator->getMetadataFactory());
    }

    protected function loadTypeExtensions()
    {
        return array(
            new Symfony_Component_Form_Extension_Validator_Type_FormTypeValidatorExtension($this->validator),
            new Symfony_Component_Form_Extension_Validator_Type_RepeatedTypeValidatorExtension(),
        );
    }
}
