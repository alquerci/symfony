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
 * This provider uses a Symfony2 Symfony_Component_HttpFoundation_Session_Session object to retrieve the user's
 * session ID.
 *
 * @see DefaultCsrfProvider
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Extension_Csrf_CsrfProvider_SessionCsrfProvider extends Symfony_Component_Form_Extension_Csrf_CsrfProvider_DefaultCsrfProvider
{
    /**
     * The user session from which the session ID is returned
     * @var Symfony_Component_HttpFoundation_Session_Session
     */
    protected $session;

    /**
     * Initializes the provider with a Symfony_Component_HttpFoundation_Session_Session object and a secret value.
     *
     * A recommended value for the secret is a generated value with at least
     * 32 characters and mixed letters, digits and special characters.
     *
     * @param Symfony_Component_HttpFoundation_Session_Session $session The user session
     * @param string  $secret  A secret value included in the CSRF token
     */
    public function __construct(Symfony_Component_HttpFoundation_Session_Session $session, $secret)
    {
        parent::__construct($secret);

        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSessionId()
    {
        $this->session->start();

        return $this->session->getId();
    }
}
