<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_TestHttpKernel extends Symfony_Component_HttpKernel_HttpKernel implements Symfony_Component_HttpKernel_Controller_ControllerResolverInterface
{
    public function __construct()
    {
        parent::__construct(new Symfony_Component_EventDispatcher_EventDispatcher(), $this);
    }

    public function getController(Symfony_Component_HttpFoundation_Request $request)
    {
        return array($this, 'callController');
    }

    public function getArguments(Symfony_Component_HttpFoundation_Request $request, $controller)
    {
        return array($request);
    }

    public function callController(Symfony_Component_HttpFoundation_Request $request)
    {
        return new Symfony_Component_HttpFoundation_Response('Request: '.$request->getRequestUri());
    }
}
