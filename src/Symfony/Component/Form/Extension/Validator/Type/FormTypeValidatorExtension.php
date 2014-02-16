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
class Symfony_Component_Form_Extension_Validator_Type_FormTypeValidatorExtension extends Symfony_Component_Form_AbstractTypeExtension
{
    /**
     * @var Symfony_Component_Validator_ValidatorInterface
     */
    private $validator;

    /**
     * @var Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapper
     */
    private $violationMapper;

    public function __construct(Symfony_Component_Validator_ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->violationMapper = new Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapper();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(Symfony_Component_Form_FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new Symfony_Component_Form_Extension_Validator_EventListener_ValidationListener($this->validator, $this->violationMapper));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(Symfony_Component_OptionsResolver_OptionsResolverInterface $resolver)
    {
        // BC clause
        $constraints = array(
            new Symfony_Component_Form_Extension_Validator_Type_FormTypeValidatorExtensionClosures(),
            'setDefaultOptionsConstraints'
        );

        // Make sure that validation groups end up as null, closure or array
        $validationGroupsNormalizer = array(
            new Symfony_Component_Form_Extension_Validator_Type_FormTypeValidatorExtensionClosures(),
            'setDefaultOptionsValidationGroupsNormalizer'
        );

        // Constraint should always be converted to an array
        $constraintsNormalizer = array(
            new Symfony_Component_Form_Extension_Validator_Type_FormTypeValidatorExtensionClosures(),
            'setDefaultOptionsConstraintsNormalizer'
        );

        $resolver->setDefaults(array(
            'error_mapping'              => array(),
            'validation_groups'          => null,
            // "validation_constraint" is deprecated. Use "constraints".
            'validation_constraint'      => null,
            'constraints'                => $constraints,
            'cascade_validation'         => false,
            'invalid_message'            => 'This value is not valid.',
            'invalid_message_parameters' => array(),
            'extra_fields_message'       => 'This form should not contain extra fields.',
            'post_max_size_message'      => 'The uploaded file was too large. Please try to upload a smaller file.',
        ));

        $resolver->setNormalizers(array(
            'validation_groups' => $validationGroupsNormalizer,
            'constraints'       => $constraintsNormalizer,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}

class Symfony_Component_Form_Extension_Validator_Type_FormTypeValidatorExtensionClosures
{
    public function setDefaultOptionsConstraints(Symfony_Component_OptionsResolver_Options $options)
    {
        return $options['validation_constraint'];
    }

    public function setDefaultOptionsValidationGroupsNormalizer(Symfony_Component_OptionsResolver_Options $options, $groups)
    {
        if (empty($groups)) {
            return null;
        }

        if (is_callable($groups)) {
            return $groups;
        }

        return (array) $groups;
    }

    public function setDefaultOptionsConstraintsNormalizer(Symfony_Component_OptionsResolver_Options $options, $constraints)
    {
        return is_object($constraints) ? array($constraints) : (array) $constraints;
    }
}
