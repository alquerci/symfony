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
 * Stores settings for creating a new validator and creates validators
 *
 * The methods in this class are chainable, i.e. they return the context
 * object itself. When you have finished configuring the new validator, call
 * getValidator() to create the it.
 *
 * <code>
 * $validator = $context
 *     ->setClassMetadataFactory($customFactory)
 *     ->getValidator();
 * </code>
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
 *             {@link Validation::createValidatorBuilder()} instead.
 */
interface Symfony_Component_Validator_ValidatorContextInterface
{
    /**
     * Sets the class metadata factory used in the new validator
     *
     * @param Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface $classMetadataFactory The factory instance
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidatorBuilder()} instead.
     */
    public function setClassMetadataFactory(Symfony_Component_Validator_Mapping_ClassMetadataFactoryInterface $classMetadataFactory);

    /**
     * Sets the constraint validator factory used in the new validator
     *
     * @param Symfony_Component_Validator_ConstraintValidatorFactoryInterface $constraintValidatorFactory The factory instance
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidatorBuilder()} instead.
     */
    public function setConstraintValidatorFactory(Symfony_Component_Validator_ConstraintValidatorFactoryInterface $constraintValidatorFactory);

    /**
     * Creates a new validator with the settings stored in this context
     *
     * @return Symfony_Component_Validator_ValidatorInterface   The new validator
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3. Use
     *             {@link Symfony_Component_Validator_Validation::createValidator()} instead.
     */
    public function getValidator();
}
