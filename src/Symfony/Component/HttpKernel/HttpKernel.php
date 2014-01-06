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
 * HttpKernel notifies events to convert a Request object to a Response one.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_HttpKernel_HttpKernel implements Symfony_Component_HttpKernel_HttpKernelInterface, Symfony_Component_HttpKernel_TerminableInterface
{
    protected $dispatcher;
    protected $resolver;

    /**
     * Constructor
     *
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface    $dispatcher An EventDispatcherInterface instance
     * @param Symfony_Component_HttpKernel_Controller_ControllerResolverInterface $resolver   A ControllerResolverInterface instance
     *
     * @api
     */
    public function __construct(Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher, Symfony_Component_HttpKernel_Controller_ControllerResolverInterface $resolver)
    {
        $this->dispatcher = $dispatcher;
        $this->resolver = $resolver;
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     * @param integer $type    The type of the request
     *                          (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param Boolean $catch Whether to catch exceptions or not
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     *
     * @throws Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        try {
            return $this->handleRaw($request, $type);
        } catch (Exception $e) {
            if (false === $catch) {
                throw $e;
            }

            return $this->handleException($e, $request, $type);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function terminate(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_HttpFoundation_Response $response)
    {
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::TERMINATE, new Symfony_Component_HttpKernel_Event_PostResponseEvent($this, $request, $response));
    }

    /**
     * Handles a request to convert it to a response.
     *
     * Exceptions are not caught.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     * @param integer $type    The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     *
     * @throws LogicException If one of the listener does not behave as expected
     * @throws Symfony_Component_HttpKernel_Exception_NotFoundHttpException When controller cannot be found
     */
    private function handleRaw(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernel::MASTER_REQUEST)
    {
        // request
        $event = new Symfony_Component_HttpKernel_Event_GetResponseEvent($this, $request, $type);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::REQUEST, $event);

        if ($event->hasResponse()) {
            return $this->filterResponse($event->getResponse(), $request, $type);
        }

        // load controller
        if (false === $controller = $this->resolver->getController($request)) {
            throw new Symfony_Component_HttpKernel_Exception_NotFoundHttpException(sprintf('Unable to find the controller for path "%s". Maybe you forgot to add the matching route in your routing configuration?', $request->getPathInfo()));
        }

        $event = new Symfony_Component_HttpKernel_Event_FilterControllerEvent($this, $controller, $request, $type);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::CONTROLLER, $event);
        $controller = $event->getController();

        // controller arguments
        $arguments = $this->resolver->getArguments($request, $controller);

        // call controller
        if (is_object($controller) && method_exists($controller, '__invoke')) {
            $response = call_user_func_array(array($controller, '__invoke'), $arguments);
        } else {
            $response = call_user_func_array($controller, $arguments);
        }

        // view
        if (!$response instanceof Symfony_Component_HttpFoundation_Response) {
            $event = new Symfony_Component_HttpKernel_Event_GetResponseForControllerResultEvent($this, $request, $type, $response);
            $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::VIEW, $event);

            if ($event->hasResponse()) {
                $response = $event->getResponse();
            }

            if (!$response instanceof Symfony_Component_HttpFoundation_Response) {
                $msg = sprintf('The controller must return a response (%s given).', $this->varToString($response));

                // the user may have forgotten to return something
                if (null === $response) {
                    $msg .= ' Did you forget to add a return statement somewhere in your controller?';
                }
                throw new LogicException($msg);
            }
        }

        return $this->filterResponse($response, $request, $type);
    }

    /**
     * Filters a response object.
     *
     * @param Symfony_Component_HttpFoundation_Response $response A Response instance
     * @param Symfony_Component_HttpFoundation_Request  $request  A error message in case the response is not a Response object
     * @param integer  $type     The type of the request (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     *
     * @return Symfony_Component_HttpFoundation_Response The filtered Response instance
     *
     * @throws RuntimeException if the passed object is not a Response instance
     */
    private function filterResponse(Symfony_Component_HttpFoundation_Response $response, Symfony_Component_HttpFoundation_Request $request, $type)
    {
        $event = new Symfony_Component_HttpKernel_Event_FilterResponseEvent($this, $request, $type, $response);

        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::RESPONSE, $event);

        return $event->getResponse();
    }

    /**
     * Handles an exception by trying to convert it to a Response.
     *
     * @param Exception $e       An \Exception instance
     * @param Symfony_Component_HttpFoundation_Request    $request A Request instance
     * @param integer    $type    The type of the request
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     *
     * @throws Exception
     */
    private function handleException(Exception $e, $request, $type)
    {
        $event = new Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent($this, $request, $type, $e);
        $this->dispatcher->dispatch(Symfony_Component_HttpKernel_KernelEvents::EXCEPTION, $event);

        // a listener might have replaced the exception
        $e = $event->getException();

        if (!$event->hasResponse()) {
            throw $e;
        }

        $response = $event->getResponse();

        // the developer asked for a specific status code
        if ($response->headers->has('X-Status-Code')) {
            $response->setStatusCode($response->headers->get('X-Status-Code'));

            $response->headers->remove('X-Status-Code');
        } elseif (!$response->isClientError() && !$response->isServerError() && !$response->isRedirect()) {
            // ensure that we actually have an error response
            if ($e instanceof Symfony_Component_HttpKernel_Exception_HttpExceptionInterface) {
                // keep the HTTP status code and headers
                $response->setStatusCode($e->getStatusCode());
                $response->headers->add($e->getHeaders());
            } else {
                $response->setStatusCode(500);
            }
        }

        try {
            return $this->filterResponse($response, $request, $type);
        } catch (Exception $e) {
            return $response;
        }
    }

    private function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf("Array(%s)", implode(', ', $a));
        }

        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'false';
        }

        if (true === $var) {
            return 'true';
        }

        return (string) $var;
    }
}
