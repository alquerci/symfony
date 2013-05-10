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
 * Allows to create a response for the return value of a controller
 *
 * Call setResponse() to set the response that will be returned for the
 * current request. The propagation of this event is stopped as soon as a
 * response is set.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_HttpKernel_Event_GetResponseForControllerResultEvent extends Symfony_Component_HttpKernel_Event_GetResponseEvent
{
    /**
     * The return value of the controller
     *
     * @var mixed
     */
    private $controllerResult;

    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, Symfony_Component_HttpFoundation_Request $request, $requestType, $controllerResult)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->controllerResult = $controllerResult;
    }

    /**
     * Returns the return value of the controller.
     *
     * @return mixed The controller return value
     *
     * @api
     */
    public function getControllerResult()
    {
        return $this->controllerResult;
    }

    /**
     * Assigns the return value of the controller.
     *
     * @param mixed The controller return value
     *
     * @api
     */
    public function setControllerResult($controllerResult)
    {
        $this->controllerResult = $controllerResult;
    }
}
