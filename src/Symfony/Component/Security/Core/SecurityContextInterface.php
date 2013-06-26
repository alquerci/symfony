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
 * The SecurityContextInterface.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Core_SecurityContextInterface
{
    const ACCESS_DENIED_ERROR  = '_security.403_error';
    const AUTHENTICATION_ERROR = '_security.last_error';
    const LAST_USERNAME        = '_security.last_username';

    /**
     * Returns the current security token.
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface|null A TokenInterface instance or null if no authentication information is available
     */
    public function getToken();

    /**
     * Sets the authentication token.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     */
    public function setToken(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null);

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param array $attributes
     * @param mixed $object
     *
     * @return Boolean
     */
    public function isGranted($attributes, $object = null);
}
