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
 * Default implementation of {@link ValidatorInterface}.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Validator_Validator implements Symfony_Component_Validator_ValidatorInterface
{
    /**
     * @var Symfony_Component_Validator_MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var Symfony_Component_Validator_ConstraintValidatorFactoryInterface
     */
    private $validatorFactory;

    /**
     * @var Symfony_Component_Translation_TranslatorInterface
     */
    private $translator;

    /**
     * @var null|string
     */
    private $translationDomain;

    /**
     * @var array
     */
    private $objectInitializers;

    public function __construct(
        Symfony_Component_Validator_MetadataFactoryInterface $metadataFactory,
        Symfony_Component_Validator_ConstraintValidatorFactoryInterface $validatorFactory,
        Symfony_Component_Translation_TranslatorInterface $translator,
        $translationDomain = 'validators',
        array $objectInitializers = array()
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->validatorFactory = $validatorFactory;
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
        $this->objectInitializers = $objectInitializers;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadataFor($value)
    {
        return $this->metadataFactory->getMetadataFor($value);
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, $groups = null, $traverse = false, $deep = false)
    {
        $visitor = $this->createVisitor($value);

        foreach ($this->resolveGroups($groups) as $group) {
            $visitor->validate($value, $group, '');
        }

        return $visitor->getViolations();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Symfony_Component_Validator_Exception_ValidatorException If the metadata for the value does not support properties.
     */
    public function validateProperty($containingValue, $property, $groups = null)
    {
        $visitor = $this->createVisitor($containingValue);
        $metadata = $this->metadataFactory->getMetadataFor($containingValue);

        if (!$metadata instanceof Symfony_Component_Validator_PropertyMetadataContainerInterface) {
            $valueAsString = is_scalar($containingValue)
                ? '"' . $containingValue . '"'
                : 'the value of type ' . gettype($containingValue);

            throw new Symfony_Component_Validator_Exception_ValidatorException(sprintf('The metadata for ' . $valueAsString . ' does not support properties.'));
        }

        foreach ($this->resolveGroups($groups) as $group) {
            if (!$metadata->hasPropertyMetadata($property)) {
                continue;
            }

            foreach ($metadata->getPropertyMetadata($property) as $propMeta) {
                $propMeta->accept($visitor, $propMeta->getPropertyValue($containingValue), $group, $property);
            }
        }

        return $visitor->getViolations();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Symfony_Component_Validator_Exception_ValidatorException If the metadata for the value does not support properties.
     */
    public function validatePropertyValue($containingValue, $property, $value, $groups = null)
    {
        $visitor = $this->createVisitor($containingValue);
        $metadata = $this->metadataFactory->getMetadataFor($containingValue);

        if (!$metadata instanceof Symfony_Component_Validator_PropertyMetadataContainerInterface) {
            $valueAsString = is_scalar($containingValue)
                ? '"' . $containingValue . '"'
                : 'the value of type ' . gettype($containingValue);

            throw new Symfony_Component_Validator_Exception_ValidatorException(sprintf('The metadata for ' . $valueAsString . ' does not support properties.'));
        }

        foreach ($this->resolveGroups($groups) as $group) {
            if (!$metadata->hasPropertyMetadata($property)) {
                continue;
            }

            foreach ($metadata->getPropertyMetadata($property) as $propMeta) {
                $propMeta->accept($visitor, $value, $group, $property);
            }
        }

        return $visitor->getViolations();
    }

    /**
     * {@inheritDoc}
     */
    public function validateValue($value, $constraints, $groups = null)
    {
        $context = new Symfony_Component_Validator_ExecutionContext($this->createVisitor(null), $this->translator, $this->translationDomain);

        $constraints = is_array($constraints) ? $constraints : array($constraints);

        foreach ($constraints as $constraint) {
            if ($constraint instanceof Symfony_Component_Validator_Constraints_Valid) {
                // Why can't the Valid constraint be executed directly?
                //
                // It cannot be executed like regular other constraints, because regular
                // constraints are only executed *if they belong to the validated group*.
                // The Valid constraint, on the other hand, is always executed and propagates
                // the group to the cascaded object. The propagated group depends on
                //
                //  * Whether a group sequence is currently being executed. Then the default
                //    group is propagated.
                //
                //  * Otherwise the validated group is propagated.

                throw new Symfony_Component_Validator_Exception_ValidatorException(
                    sprintf(
                        'The constraint %s cannot be validated. Use the method validate() instead.',
                        get_class($constraint)
                    )
                );
            }

            $context->validateValue($value, $constraint, $groups);
        }

        return $context->getViolations();
    }

    /**
     * @param mixed $root
     *
     * @return Symfony_Component_Validator_ValidationVisitor
     */
    private function createVisitor($root)
    {
        return new Symfony_Component_Validator_ValidationVisitor(
            $root,
            $this->metadataFactory,
            $this->validatorFactory,
            $this->translator,
            $this->translationDomain,
            $this->objectInitializers
        );
    }

    /**
     * @param null|string|string[] $groups
     *
     * @return string[]
     */
    private function resolveGroups($groups)
    {
        return $groups ? (array) $groups : array(Symfony_Component_Validator_Constraint::DEFAULT_GROUP);
    }
}
