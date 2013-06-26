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
 * This class is used for testing purposes, and is not really suited for production.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_Authentication_RememberMe_InMemoryTokenProvider implements Symfony_Component_Security_Core_Authentication_RememberMe_TokenProviderInterface
{
    private $tokens = array();

    public function loadTokenBySeries($series)
    {
        if (!isset($this->tokens[$series])) {
            throw new Symfony_Component_Security_Core_Exception_TokenNotFoundException('No token found.');
        }

        return $this->tokens[$series];
    }

    public function updateToken($series, $tokenValue, DateTime $lastUsed)
    {
        if (!isset($this->tokens[$series])) {
            throw new Symfony_Component_Security_Core_Exception_TokenNotFoundException('No token found.');
        }

        $token = new Symfony_Component_Security_Core_Authentication_RememberMe_PersistentToken(
            $this->tokens[$series]->getClass(),
            $this->tokens[$series]->getUsername(),
            $series,
            $tokenValue,
            $lastUsed
        );
        $this->tokens[$series] = $token;
    }

    public function deleteTokenBySeries($series)
    {
        unset($this->tokens[$series]);
    }

    public function createNewToken(Symfony_Component_Security_Core_Authentication_RememberMe_PersistentTokenInterface $token)
    {
        $this->tokens[$token->getSeries()] = $token;
    }
}
