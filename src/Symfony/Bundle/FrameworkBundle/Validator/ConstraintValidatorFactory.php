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
 * Uses a service container to create constraint validators.
 *
 * A constraint validator should be tagged as "validator.constraint_validator"
 * in the service container and include an "alias" attribute:
 *
 *     <service id="some_doctrine_validator">
 *         <argument type="service" id="doctrine.orm.some_entity_manager" />
 *         <tag name="validator.constraint_validator" alias="some_alias" />
 *     </service>
 *
 * A constraint may then return this alias in its validatedBy() method:
 *
 *     public function validatedBy()
 *     {
 *         return 'some_alias';
 *     }
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Validator_ConstraintValidatorFactory implements Symfony_Component_Validator_ConstraintValidatorFactoryInterface
{
    protected $container;
    protected $validators;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface $container  The service container
     * @param array              $validators An array of validators
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container, array $validators = array())
    {
        $this->container = $container;
        $this->validators = $validators;
    }

    /**
     * Returns the validator for the supplied constraint.
     *
     * @param Symfony_Component_Validator_Constraint $constraint A constraint
     *
     * @return Symfony_Component_Validator_ConstraintValidator A validator for the supplied constraint
     */
    public function getInstance(Symfony_Component_Validator_Constraint $constraint)
    {
        $name = $constraint->validatedBy();

        if (!isset($this->validators[$name])) {
            $this->validators[$name] = new $name();
        } elseif (is_string($this->validators[$name])) {
            $this->validators[$name] = $this->container->get($this->validators[$name]);
        }

        return $this->validators[$name];
    }
}
