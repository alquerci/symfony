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
 * AuthenticationException is the base class for all authentication exceptions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Alexander <iam.asm89@gmail.com>
 */
class Symfony_Component_Security_Core_Exception_AuthenticationException extends Symfony_Component_Security_Core_Exception_RuntimeException implements Serializable
{
    private $token;

    /**
     * Get the token.
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the token.
     *
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token
     */
    public function setToken(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        $this->token = $token;
    }

    public function serialize()
    {
        return serialize(array(
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line,
        ));
    }

    public function unserialize($str)
    {
        list(
            $this->token,
            $this->code,
            $this->message,
            $this->file,
            $this->line
        ) = unserialize($str);
    }

    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey()
    {
        return 'An authentication exception occurred.';
    }

    /**
     * Message data to be used by the translation component.
     *
     * @return array
     */
    public function getMessageData()
    {
        return array();
    }
}
