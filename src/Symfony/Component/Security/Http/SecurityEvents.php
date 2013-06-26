<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

final class Symfony_Component_Security_Http_SecurityEvents
{
    /**
     * The INTERACTIVE_LOGIN event occurs after a user is logged in
     * interactively for authentication based on http, cookies or X509.
     *
     * The event listener method receives a
     * Symfony_Component_Security_Http_Event_InteractiveLoginEvent instance.
     *
     * @var string
     */
    const INTERACTIVE_LOGIN = 'security.interactive_login';

    /**
     * The SWITCH_USER event occurs before switch to another user and
     * before exit from an already switched user.
     *
     * The event listener method receives a
     * Symfony_Component_Security_Http_Event_SwitchUserEvent instance.
     *
     * @var string
     */
    const SWITCH_USER = 'security.switch_user';
}
