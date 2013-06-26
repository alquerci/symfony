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
 * Interface for resolving the authentication status of a given token.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Core_Authentication_AuthenticationTrustResolverInterface
{
    /**
     * Resolves whether the passed token implementation is authenticated
     * anonymously.
     *
     * If null is passed, the method must return false.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     *
     * @return Boolean
     */
    public function isAnonymous(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null);

    /**
     * Resolves whether the passed token implementation is authenticated
     * using remember-me capabilities.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     *
     * @return Boolean
     */
    public function isRememberMe(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null);

    /**
     * Resolves whether the passed token implementation is fully authenticated.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     *
     * @return Boolean
     */
    public function isFullFledged(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token = null);
}
