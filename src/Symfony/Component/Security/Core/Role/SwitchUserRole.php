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
 * SwitchUserRole is used when the current user temporarily impersonates
 * another one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Core_Role_SwitchUserRole extends Symfony_Component_Security_Core_Role_Role
{
    private $source;

    /**
     * Constructor.
     *
     * @param string         $role   The role as a string
     * @param Symfony_Component_Security_Core_Authentication_Token_TokenInterface $source The original token
     */
    public function __construct($role, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $source)
    {
        parent::__construct($role);

        $this->source = $source;
    }

    /**
     * Returns the original Token.
     *
     * @return Symfony_Component_Security_Core_Authentication_Token_TokenInterface The original TokenInterface instance
     */
    public function getSource()
    {
        return $this->source;
    }
}
