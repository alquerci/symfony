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
 * AuthenticationProviderInterface is the interface for all authentication
 * providers.
 *
 * Concrete implementations processes specific Token instances.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface extends Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface
{
    /**
     * Checks whether this provider supports the given token.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token A TokenInterface instance
     *
     * @return Boolean true if the implementation supports the Token, false otherwise
     */
     public function supports(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token);
}
