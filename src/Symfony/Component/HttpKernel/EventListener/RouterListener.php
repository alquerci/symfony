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
 * Initializes the context from the request and sets request attributes based on a matching route.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_EventListener_RouterListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $matcher;
    private $context;
    private $logger;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Routing_Matcher_UrlMatcherInterface|Symfony_Component_Routing_Matcher_RequestMatcherInterface $matcher The Url or Request matcher
     * @param Symfony_Component_Routing_RequestContext|null                         $context The RequestContext (can be null when $matcher implements RequestContextAwareInterface)
     * @param Psr_Log_LoggerInterface|null                        $logger  The logger
     *
     * @throws InvalidArgumentException
     */
    public function __construct($matcher, Symfony_Component_Routing_RequestContext $context = null, Psr_Log_LoggerInterface $logger = null)
    {
        if (!$matcher instanceof Symfony_Component_Routing_Matcher_UrlMatcherInterface && !$matcher instanceof Symfony_Component_Routing_Matcher_RequestMatcherInterface) {
            throw new InvalidArgumentException('Matcher must either implement UrlMatcherInterface or RequestMatcherInterface.');
        }

        if (null === $context && !$matcher instanceof Symfony_Component_Routing_RequestContextAwareInterface) {
            throw new InvalidArgumentException('You must either pass a RequestContext or the matcher must implement RequestContextAwareInterface.');
        }

        $this->matcher = $matcher;
        $this->context = $context ? $context : $matcher->getContext();
        $this->logger = $logger;
    }

    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // initialize the context that is also used by the generator (assuming matcher and generator share the same context instance)
        $this->context->fromRequest($request);

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        // add attributes based on the request (routing)
        try {
            // matching a request is more powerful than matching a URL path + context, so try that first
            if ($this->matcher instanceof Symfony_Component_Routing_Matcher_RequestMatcherInterface) {
                $parameters = $this->matcher->matchRequest($request);
            } else {
                $parameters = $this->matcher->match($request->getPathInfo());
            }

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
            }

            $request->attributes->add($parameters);
            unset($parameters['_route']);
            unset($parameters['_controller']);
            $request->attributes->set('_route_params', $parameters);
        } catch (Symfony_Component_Routing_Exception_ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

            throw new Symfony_Component_HttpKernel_Exception_NotFoundHttpException($message, $e);
        } catch (Symfony_Component_Routing_Exception_MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), strtoupper(implode(', ', $e->getAllowedMethods())));

            throw new Symfony_Component_HttpKernel_Exception_MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }
    }

    private function parametersToString(array $parameters)
    {
        $pieces = array();
        foreach ($parameters as $key => $val) {
            $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
        }

        return implode(', ', $pieces);
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::REQUEST => array(array('onKernelRequest', 32)),
        );
    }
}
