<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Core_Validator_Constraints_UserPasswordValidator extends Symfony_Component_Validator_ConstraintValidator
{
    private $securityContext;
    private $encoderFactory;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $securityContext, Symfony_Component_Security_Core_Encoder_EncoderFactoryInterface $encoderFactory)
    {
        $this->securityContext = $securityContext;
        $this->encoderFactory = $encoderFactory;
    }

    public function validate($password, Symfony_Component_Validator_Constraint $constraint)
    {
        $user = $this->securityContext->getToken()->getUser();

        if (!$user instanceof Symfony_Component_Security_Core_User_UserInterface) {
            throw new Symfony_Component_Validator_Exception_ConstraintDefinitionException('The User object must implement the UserInterface interface.');
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
            $this->context->addViolation($constraint->message);
        }
    }
}
