<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_HttpCache_TestMultipleHttpKernel extends Symfony_Component_HttpKernel_HttpKernel implements Symfony_Component_HttpKernel_Controller_ControllerResolverInterface
{
    protected $bodies;
    protected $statuses;
    protected $headers;
    protected $catch;
    protected $call;
    protected $backendRequest;

    public function __construct($responses)
    {
        $this->bodies   = array();
        $this->statuses = array();
        $this->headers  = array();
        $this->call     = false;

        foreach ($responses as $response) {
            $this->bodies[]   = $response['body'];
            $this->statuses[] = $response['status'];
            $this->headers[]  = $response['headers'];
        }

        parent::__construct(new Symfony_Component_EventDispatcher_EventDispatcher(), $this);
    }

    public function getBackendRequest()
    {
        return $this->backendRequest;
    }

    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $catch = false)
    {
        $this->backendRequest = $request;

        return parent::handle($request, $type, $catch);
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
        $this->called = true;

        $response = new Symfony_Component_HttpFoundation_Response(array_shift($this->bodies), array_shift($this->statuses), array_shift($this->headers));

        return $response;
    }

    public function hasBeenCalled()
    {
        return $this->called;
    }

    public function reset()
    {
        $this->call = false;
    }
}
