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
class Symfony_Component_Form_Extension_Validator_Constraints_FormValidator extends Symfony_Component_Validator_ConstraintValidator
{
    /**
     * @var Symfony_Component_Form_Extension_Validator_Util_ServerParams
     */
    private $serverParams;

    /**
     * Creates a validator with the given server parameters.
     *
     * @param Symfony_Component_Form_Extension_Validator_Util_ServerParams $params The server parameters. Default
     *                             parameters are created if null.
     */
    public function __construct(Symfony_Component_Form_Extension_Validator_Util_ServerParams $params = null)
    {
        $this->serverParams = $params ?: new Symfony_Component_Form_Extension_Validator_Util_ServerParams();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($form, Symfony_Component_Validator_Constraint $constraint)
    {
        if (!$form instanceof Symfony_Component_Form_FormInterface) {
            return;
        }

        /* @var FormInterface $form */
        $config = $form->getConfig();

        if ($form->isSynchronized()) {
            // Validate the form data only if transformation succeeded
            $groups = self::getValidationGroups($form);

            // Validate the data against its own constraints
            if (self::allowDataWalking($form)) {
                foreach ($groups as $group) {
                    $this->context->validate($form->getData(), 'data', $group, true);
                }
            }

            // Validate the data against the constraints defined
            // in the form
            $constraints = $config->getOption('constraints');
            foreach ($constraints as $constraint) {
                foreach ($groups as $group) {
                    if (in_array($group, $constraint->groups)) {
                        $this->context->validateValue($form->getData(), $constraint, 'data', $group);

                        // Prevent duplicate validation
                        continue 2;
                    }
                }
            }
        } else {
            $childrenSynchronized = true;

            foreach ($form as $child) {
                if (!$child->isSynchronized()) {
                    $childrenSynchronized = false;
                    break;
                }
            }

            // Mark the form with an error if it is not synchronized BUT all
            // of its children are synchronized. If any child is not
            // synchronized, an error is displayed there already and showing
            // a second error in its parent form is pointless, or worse, may
            // lead to duplicate errors if error bubbling is enabled on the
            // child.
            // See also https://github.com/symfony/symfony/issues/4359
            if ($childrenSynchronized) {
                $clientDataAsString = is_scalar($form->getViewData())
                    ? (string) $form->getViewData()
                    : gettype($form->getViewData());

                $this->context->addViolation(
                    $config->getOption('invalid_message'),
                    array_replace(array('{{ value }}' => $clientDataAsString), $config->getOption('invalid_message_parameters')),
                    $form->getViewData(),
                    null,
                    Symfony_Component_Form_Extension_Validator_Constraints_Form::ERR_INVALID
                );
            }
        }

        // Mark the form with an error if it contains extra fields
        if (count($form->getExtraData()) > 0) {
            $this->context->addViolation(
                $config->getOption('extra_fields_message'),
                array('{{ extra_fields }}' => implode('", "', array_keys($form->getExtraData()))),
                $form->getExtraData()
            );
        }

        // Mark the form with an error if the uploaded size was too large
        $length = $this->serverParams->getContentLength();

        if ($form->isRoot() && null !== $length) {
            $max = $this->serverParams->getPostMaxSize();

            if (null !== $max && $length > $max) {
                $this->context->addViolation(
                    $config->getOption('post_max_size_message'),
                    array('{{ max }}' => $this->serverParams->getNormalizedIniPostMaxSize()),
                    $length
                );
            }
        }
    }

    /**
     * Returns whether the data of a form may be walked.
     *
     * @param  Symfony_Component_Form_FormInterface $form The form to test.
     *
     * @return Boolean Whether the graph walker may walk the data.
     */
    private static function allowDataWalking(Symfony_Component_Form_FormInterface $form)
    {
        $data = $form->getData();

        // Scalar values cannot have mapped constraints
        if (!is_object($data) && !is_array($data)) {
            return false;
        }

        // Root forms are always validated
        if ($form->isRoot()) {
            return true;
        }

        // Non-root forms are validated if validation cascading
        // is enabled in all ancestor forms
        while (null !== ($form = $form->getParent())) {
            if (!$form->getConfig()->getOption('cascade_validation')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the validation groups of the given form.
     *
     * @param  Symfony_Component_Form_FormInterface $form The form.
     *
     * @return array The validation groups.
     */
    private static function getValidationGroups(Symfony_Component_Form_FormInterface $form)
    {
        do {
            $groups = $form->getConfig()->getOption('validation_groups');

            if (null !== $groups) {
                if (is_callable($groups)) {
                    $groups = call_user_func($groups, $form);
                }

                return (array) $groups;
            }

            $form = $form->getParent();
        } while (null !== $form);

        return array(Symfony_Component_Validator_Constraint::DEFAULT_GROUP);
    }
}
