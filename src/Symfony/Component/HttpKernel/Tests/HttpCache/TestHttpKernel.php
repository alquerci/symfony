<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_HttpKernel_Tests_HttpCache_TestHttpKernel extends Symfony_Component_HttpKernel_HttpKernel implements Symfony_Component_HttpKernel_Controller_ControllerResolverInterface
{
    protected $body;
    protected $status;
    protected $headers;
    protected $called;
    protected $customizer;
    protected $catch;
    protected $backendRequest;

    public function __construct($body, $status, $headers, $customizer = null)
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
        $this->customizer = $customizer;
        $this->called = false;
        $this->catch = false;

        parent::__construct(new Symfony_Component_EventDispatcher_EventDispatcher(), $this);
    }

    public function getBackendRequest()
    {
        return $this->backendRequest;
    }

    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $catch = false)
    {
        $this->catch = $catch;
        $this->backendRequest = $request;

        return parent::handle($request, $type, $catch);
    }

    public function isCatchingExceptions()
    {
        return $this->catch;
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

        $response = new Symfony_Component_HttpFoundation_Response($this->body, $this->status, $this->headers);

        if (null !== $this->customizer) {
            call_user_func($this->customizer, $request, $response);
        }

        return $response;
    }

    public function hasBeenCalled()
    {
        return $this->called;
    }

    public function reset()
    {
        $this->called = false;
    }
}
