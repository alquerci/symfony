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
class Symfony_Component_Form_Extension_Csrf_EventListener_CsrfValidationListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    /**
     * The name of the CSRF field
     * @var string
     */
    private $fieldName;

    /**
     * The provider for generating and validating CSRF tokens
     * @var Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface
     */
    private $csrfProvider;

    /**
     * A text mentioning the intention of the CSRF token
     *
     * Validation of the token will only succeed if it was generated in the
     * same session and with the same intention.
     *
     * @var string
     */
    private $intention;

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_Form_FormEvents::PRE_BIND => 'preBind',
        );
    }

    public function __construct($fieldName, Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider, $intention)
    {
        $this->fieldName = $fieldName;
        $this->csrfProvider = $csrfProvider;
        $this->intention = $intention;
    }

    public function preBind(Symfony_Component_Form_FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($form->isRoot() && $form->getConfig()->getOption('compound')) {
            if (!isset($data[$this->fieldName]) || !$this->csrfProvider->isCsrfTokenValid($this->intention, $data[$this->fieldName])) {
                $form->addError(new Symfony_Component_Form_FormError('The CSRF token is invalid. Please try to resubmit the form.'));
            }

            if (is_array($data)) {
                unset($data[$this->fieldName]);
            }
        }

        $event->setData($data);
    }
}
