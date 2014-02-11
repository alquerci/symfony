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
 * Renders a URI that represents a resource fragment.
 *
 * This class handles the rendering of resource fragments that are included into
 * a main resource. The handling of the rendering is managed by specialized renderers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @see Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface
 */
class Symfony_Component_HttpKernel_Fragment_FragmentHandler implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $debug;
    private $renderers;
    private $requests;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface[] $renderers An array of FragmentRendererInterface instances
     * @param Boolean                     $debug     Whether the debug mode is enabled or not
     */
    public function __construct(array $renderers = array(), $debug = false)
    {
        $this->renderers = array();
        foreach ($renderers as $renderer) {
            $this->addRenderer($renderer);
        }
        $this->debug = $debug;
        $this->requests = array();
    }

    /**
     * Adds a renderer.
     *
     * @param Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface $strategy A FragmentRendererInterface instance
     */
    public function addRenderer(Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface $renderer)
    {
        $this->renderers[$renderer->getName()] = $renderer;
    }

    /**
     * Stores the Request object.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     */
    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        array_unshift($this->requests, $event->getRequest());
    }

    /**
     * Removes the most recent Request object.
     *
     * @param Symfony_Component_HttpKernel_Event_FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onKernelResponse(Symfony_Component_HttpKernel_Event_FilterResponseEvent $event)
    {
        array_shift($this->requests);
    }

    /**
     * Renders a URI and returns the Response content.
     *
     * Available options:
     *
     *  * ignore_errors: true to return an empty string in case of an error
     *
     * @param string|Symfony_Component_HttpKernel_Controller_ControllerReference $uri      A URI as a string or a ControllerReference instance
     * @param string                     $renderer The renderer name
     * @param array                      $options  An array of options
     *
     * @return string|null The Response content or null when the Response is streamed
     *
     * @throws InvalidArgumentException when the renderer does not exist
     * @throws RuntimeException         when the Response is not successful
     */
    public function render($uri, $renderer = 'inline', array $options = array())
    {
        if (!isset($options['ignore_errors'])) {
            $options['ignore_errors'] = !$this->debug;
        }

        if (!isset($this->renderers[$renderer])) {
            throw new InvalidArgumentException(sprintf('The "%s" renderer does not exist.', $renderer));
        }

        return $this->deliver($this->renderers[$renderer]->render($uri, $this->requests[0], $options));
    }

    /**
     * Delivers the Response as a string.
     *
     * When the Response is a StreamedResponse, the content is streamed immediately
     * instead of being returned.
     *
     * @param Symfony_Component_HttpFoundation_Response $response A Response instance
     *
     * @return string|null The Response content or null when the Response is streamed
     *
     * @throws RuntimeException when the Response is not successful
     */
    protected function deliver(Symfony_Component_HttpFoundation_Response $response)
    {
        if (!$response->isSuccessful()) {
            throw new RuntimeException(sprintf('Error when rendering "%s" (Status code is %s).', $this->requests[0]->getUri(), $response->getStatusCode()));
        }

        if (!$response instanceof Symfony_Component_HttpFoundation_StreamedResponse) {
            return $response->getContent();
        }

        $response->sendContent();
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::REQUEST  => 'onKernelRequest',
            Symfony_Component_HttpKernel_KernelEvents::RESPONSE => 'onKernelResponse',
        );
    }

    // to be removed in 2.3
    public function fixOptions(array $options)
    {
        // support for the standalone option is @deprecated in 2.2 and replaced with the strategy option
        if (isset($options['standalone'])) {
            version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('The "standalone" option is deprecated in version 2.2 and replaced with the "strategy" option.', E_USER_DEPRECATED);

            // support for the true value is @deprecated in 2.2, will be removed in 2.3
            if (true === $options['standalone']) {
                version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('The "true" value for the "standalone" option is deprecated in version 2.2 and replaced with the "esi" value.', E_USER_DEPRECATED);

                $options['standalone'] = 'esi';
            } elseif (false === $options['standalone']) {
                version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('The "false" value for the "standalone" option is deprecated in version 2.2 and replaced with the "inline" value.', E_USER_DEPRECATED);

                $options['standalone'] = 'inline';
            } elseif ('js' === $options['standalone']) {
                version_compare(PHP_VERSION, '5.3.0', '>=') && trigger_error('The "js" value for the "standalone" option is deprecated in version 2.2 and replaced with the "hinclude" value.', E_USER_DEPRECATED);

                $options['standalone'] = 'hinclude';
            }

            $options['strategy'] = $options['standalone'];
            unset($options['standalone']);
        }

        return $options;
    }
}
