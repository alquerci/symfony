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
 * Adds SessionHandler functionality if available.
 *
 * @see http://php.net/sessionhandler
 */

if (version_compare(phpversion(), '5.4.0', '>=')) {
    class Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeSessionHandler extends SessionHandler {}
} else {
    class Symfony_Component_HttpFoundation_Session_Storage_Handler_NativeSessionHandler {}
}
