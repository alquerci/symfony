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
 * The default implementation of the authentication trust resolver.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_Authentication_AuthenticationTrustResolver implements Symfony_Component_Security_Core_Authentication_AuthenticationTrustResolverInterface
{
    private $anonymousClass;
    private $rememberMeClass;

    /**
     * Constructor
     *
     * @param string $anonymousClass
     * @param string $rememberMeClass
     */
    public function __construct($anonymousClass, $rememberMeClass)
    {
        $this->anonymousClass = $anonymousClass;
        $this->rememberMeClass = $rememberMeClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isAnonymous(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null)
    {
        if (null === $token) {
            return false;
        }

        return $token instanceof $this->anonymousClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isRememberMe(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null)
    {
        if (null === $token) {
            return false;
        }

        return $token instanceof $this->rememberMeClass;
    }

    /**
     * {@inheritDoc}
     */
    public function isFullFledged(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null)
    {
        if (null === $token) {
            return false;
        }

        return !$this->isAnonymous($token) && !$this->isRememberMe($token);
    }
}
