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
class Symfony_Component_Form_Extension_Validator_EventListener_ValidationListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $validator;

    private $violationMapper;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(Symfony_Component_Form_FormEvents::POST_BIND => 'validateForm');
    }

    public function __construct(Symfony_Component_Validator_ValidatorInterface $validator, Symfony_Component_Form_Extension_Validator_ViolationMapper_ViolationMapperInterface $violationMapper)
    {
        $this->validator = $validator;
        $this->violationMapper = $violationMapper;
    }

    /**
     * Validates the form and its domain object.
     *
     * @param Symfony_Component_Form_FormEvent $event The event object
     */
    public function validateForm(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->isRoot()) {
            // Validate the form in group "Default"
            $violations = $this->validator->validate($form);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    // Allow the "invalid" constraint to be put onto
                    // non-synchronized forms
                    $allowNonSynchronized = Symfony_Component_Form_Extension_Validator_Constraints_Form::ERR_INVALID === $violation->getCode();

                    $this->violationMapper->mapViolation($violation, $form, $allowNonSynchronized);
                }
            }
        }
    }
}
