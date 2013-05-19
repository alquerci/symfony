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
 * ExceptionListener.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_EventListener_ExceptionListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    protected $controller;
    protected $logger;

    public function __construct($controller, Psr_Log_LoggerInterface $logger = null)
    {
        $this->controller = $controller;
        $this->logger = $logger;
    }

    public function onKernelException(Symfony_Component_HttpKernel_Event_GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();
        $request = $event->getRequest();

        $this->logException($exception, sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()));

        $attributes = array(
            '_controller' => $this->controller,
            'exception'   => Symfony_Component_HttpKernel_Exception_FlattenException::create($exception),
            'logger'      => $this->logger instanceof Symfony_Component_HttpKernel_Log_DebugLoggerInterface ? $this->logger : null,
            'format'      => $request->getRequestFormat(),
        );

        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        try {
            $response = $event->getKernel()->handle($request, Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST, true);
        } catch (Exception $e) {
            $this->logException($exception, sprintf('Exception thrown when handling an exception (%s: %s)', get_class($e), $e->getMessage()), false);

            // set handling to false otherwise it wont be able to handle further more
            $handling = false;

            // re-throw the exception from within HttpKernel as this is a catch-all
            return;
        }

        $event->setResponse($response);

        $handling = false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::EXCEPTION => array('onKernelException', -128),
        );
    }

    /**
     * Logs an exception.
     *
     * @param \Exception $exception The original \Exception instance
     * @param string     $message   The error message to log
     * @param Boolean    $original  False when the handling of the exception thrown another exception
     */
    protected function logException(Exception $exception, $message, $original = true)
    {
        $isCritical = !$exception instanceof Symfony_Component_HttpKernel_Exception_HttpExceptionInterface || $exception->getStatusCode() >= 500;
        if (null !== $this->logger) {
            if ($isCritical) {
                $this->logger->critical($message);
            } else {
                $this->logger->error($message);
            }
        } elseif (!$original || $isCritical) {
            error_log($message);
        }
    }
}
