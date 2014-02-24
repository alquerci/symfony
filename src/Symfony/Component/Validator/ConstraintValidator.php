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
 * Base class for constraint validators
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
abstract class Symfony_Component_Validator_ConstraintValidator implements Symfony_Component_Validator_ConstraintValidatorInterface
{
    /**
     * @var Symfony_Component_Validator_ExecutionContextInterface
     */
    protected $context;

    /**
     * @var string
     *
     * @deprecated
     */
    private $messageTemplate;

    /**
     * @var array
     *
     * @deprecated
     */
    private $messageParameters;

    /**
     * {@inheritDoc}
     */
    public function initialize(Symfony_Component_Validator_ExecutionContextInterface $context)
    {
        $this->context = $context;
        $this->messageTemplate = '';
        $this->messageParameters = array();
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    public function getMessageTemplate()
    {
        trigger_error('getMessageTemplate() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);

        return $this->messageTemplate;
    }

    /**
     * {@inheritDoc}
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    public function getMessageParameters()
    {
        trigger_error('getMessageParameters() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);

        return $this->messageParameters;
    }

    /**
     * Wrapper for $this->context->addViolation()
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    protected function setMessage($template, array $parameters = array())
    {
        trigger_error('setMessage() is deprecated since version 2.1 and will be removed in 2.3.', E_USER_DEPRECATED);

        $this->messageTemplate = $template;
        $this->messageParameters = $parameters;

        if (!$this->context instanceof Symfony_Component_Validator_ExecutionContext) {
            throw new Symfony_Component_Validator_Exception_ValidatorException('ConstraintValidator::initialize() must be called before setting violation messages');
        }

        $this->context->addViolation($template, $parameters);
    }

    /**
     * Stub implementation delegating to the deprecated isValid method.
     *
     * This stub exists for BC and will be dropped in Symfony 2.3.
     *
     * @see Symfony_Component_Validator_ConstraintValidatorInterface::validate
     */
    public function validate($value, Symfony_Component_Validator_Constraint $constraint)
    {
        trigger_error('isValid() is deprecated since version 2.1 and will be removed in 2.3. Implement validate() instead.', E_USER_DEPRECATED);

        return $this->isValid($value, $constraint);
    }

    /**
     * BC variant of validate.
     *
     * @deprecated Deprecated since version 2.1, to be removed in 2.3.
     */
    protected function isValid($value, Symfony_Component_Validator_Constraint $constraint)
    {
    }
}
