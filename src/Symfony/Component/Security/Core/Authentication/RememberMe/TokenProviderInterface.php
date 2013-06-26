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
 * Interface for TokenProviders
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
interface Symfony_Component_Security_Core_Authentication_RememberMe_TokenProviderInterface
{
    /**
     * Loads the active token for the given series.
     *
     * @throws Symfony_Component_Security_Core_Exception_TokenNotFoundException if the token is not found
     *
     * @param string $series
     *
     * @return Symfony_Component_Security_Core_Authentication_RememberMe_PersistentTokenInterface
     */
    public function loadTokenBySeries($series);

    /**
     * Deletes all tokens belonging to series.
     *
     * @param string $series
     */
    public function deleteTokenBySeries($series);

    /**
     * Updates the token according to this data.
     *
     * @param string    $series
     * @param string    $tokenValue
     * @param DateTime $lastUsed
     */
    public function updateToken($series, $tokenValue, DateTime $lastUsed);

    /**
     * Creates a new token.
     *
     * @param Symfony_Component_Security_Core_Authentication_RememberMe_PersistentTokenInterface $token
     */
    public function createNewToken(Symfony_Component_Security_Core_Authentication_RememberMe_PersistentTokenInterface $token);
}
