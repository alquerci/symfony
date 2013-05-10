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
 * Allows to execute logic after a response was sent
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Symfony_Component_HttpKernel_Event_PostResponseEvent extends Symfony_Component_EventDispatcher_Event
{
    /**
     * The kernel in which this event was thrown
     * @var Symfony_Component_HttpKernel_HttpKernelInterface
     */
    private $kernel;

    private $request;

    private $response;

    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Returns the kernel in which this event was thrown.
     *
     * @return Symfony_Component_HttpKernel_HttpKernelInterface
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns the request for which this event was thrown.
     *
     * @return Symfony_Component_HttpFoundation_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the response for which this event was thrown.
     *
     * @return Symfony_Component_HttpFoundation_Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
