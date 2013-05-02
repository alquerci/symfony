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
 * Allows to filter a Response object
 *
 * You can call getResponse() to retrieve the current response. With
 * setResponse() you can set a new response that will be returned to the
 * browser.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_HttpKernel_Event_FilterResponseEvent extends Symfony_Component_HttpKernel_Event_KernelEvent
{
    /**
     * The current response object
     * @var Symfony_Component_HttpFoundation_Response
     */
    private $response;

    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, Symfony_Component_HttpFoundation_Request $request, $requestType, Symfony_Component_HttpFoundation_Response $response)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->setResponse($response);
    }

    /**
     * Returns the current response object
     *
     * @return Symfony_Component_HttpFoundation_Response
     *
     * @api
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets a new response object
     *
     * @param Symfony_Component_HttpFoundation_Response $response
     *
     * @api
     */
    public function setResponse(Symfony_Component_HttpFoundation_Response $response)
    {
        $this->response = $response;
    }
}
