<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Security_Http_Event_SwitchUserEvent extends Symfony_Component_EventDispatcher_Event
{
    private $request;

    private $targetUser;

    public function __construct(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_User_UserInterface $targetUser)
    {
        $this->request = $request;
        $this->targetUser = $targetUser;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getTargetUser()
    {
        return $this->targetUser;
    }
}
