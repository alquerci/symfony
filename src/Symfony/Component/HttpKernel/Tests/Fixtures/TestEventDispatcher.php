<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_Fixtures_TestEventDispatcher extends Symfony_Component_EventDispatcher_EventDispatcher implements Symfony_Component_EventDispatcher_Debug_TraceableEventDispatcherInterface
{
    public function getCalledListeners()
    {
        return array('foo');
    }

    public function getNotCalledListeners()
    {
        return array('bar');
    }
}
