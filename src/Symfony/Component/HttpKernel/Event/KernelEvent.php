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
 * Base class for events thrown in the HttpKernel component
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_HttpKernel_Event_KernelEvent extends Symfony_Component_EventDispatcher_Event
{
    /**
     * The kernel in which this event was thrown
     * @var Symfony_Component_HttpKernel_HttpKernelInterface
     */
    private $kernel;

    /**
     * The request the kernel is currently processing
     * @var Symfony_Component_HttpFoundation_Request
     */
    private $request;

    /**
     * The request type the kernel is currently processing.  One of
     * HttpKernelInterface::MASTER_REQUEST and HttpKernelInterface::SUB_REQUEST
     * @var integer
     */
    private $requestType;

    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, Symfony_Component_HttpFoundation_Request $request, $requestType)
    {
        $this->kernel = $kernel;
        $this->request = $request;
        $this->requestType = $requestType;
    }

    /**
     * Returns the kernel in which this event was thrown
     *
     * @return Symfony_Component_HttpKernel_HttpKernelInterface
     *
     * @api
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * Returns the request the kernel is currently processing
     *
     * @return Symfony_Component_HttpFoundation_Request
     *
     * @api
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Returns the request type the kernel is currently processing
     *
     * @return integer  One of HttpKernelInterface::MASTER_REQUEST and
     *                  HttpKernelInterface::SUB_REQUEST
     *
     * @api
     */
    public function getRequestType()
    {
        return $this->requestType;
    }
}
