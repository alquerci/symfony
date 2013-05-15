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
 * Handles content fragments represented by special URIs.
 *
 * All URL paths starting with /_fragment are handled as
 * content fragments by this listener.
 *
 * If the request does not come from a trusted IP, it throws an
 * AccessDeniedHttpException exception.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_EventListener_FragmentListener implements Symfony_Component_EventDispatcher_EventSubscriberInterface
{
    private $signer;
    private $fragmentPath;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_UriSigner $signer       A UriSigner instance
     * @param string    $fragmentPath The path that triggers this listener
     */
    public function __construct(Symfony_Component_HttpKernel_UriSigner $signer, $fragmentPath = '/_fragment')
    {
        $this->signer = $signer;
        $this->fragmentPath = $fragmentPath;
    }

    /**
     * Fixes request attributes when the path is '/_fragment'.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     *
     * @throws Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException if the request does not come from a trusted IP.
     */
    public function onKernelRequest(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->fragmentPath !== rawurldecode($request->getPathInfo())) {
            return;
        }

        $this->validateRequest($request);

        parse_str($request->query->get('_path', ''), $attributes);
        $request->attributes->add($attributes);
        $request->attributes->set('_route_params', array_replace($request->attributes->get('_route_params', array()), $attributes));
        $request->query->remove('_path');
    }

    protected function validateRequest(Symfony_Component_HttpFoundation_Request $request)
    {
        // is the Request safe?
        if (!$request->isMethodSafe()) {
            throw new Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException();
        }

        // does the Request come from a trusted IP?
        $trustedIps = array_merge($this->getLocalIpAddresses(), $request->getTrustedProxies());
        $remoteAddress = $request->server->get('REMOTE_ADDR');
        foreach ($trustedIps as $ip) {
            if (Symfony_Component_HttpFoundation_IpUtils::checkIp($remoteAddress, $ip)) {
                return;
            }
        }

        // is the Request signed?
        // we cannot use $request->getUri() here as we want to work with the original URI (no query string reordering)
        if ($this->signer->check($request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo().(null !== ($qs = $request->server->get('QUERY_STRING')) ? '?'.$qs : ''))) {
            return;
        }

        throw new Symfony_Component_HttpKernel_Exception_AccessDeniedHttpException();
    }

    protected function getLocalIpAddresses()
    {
        return array('127.0.0.1', 'fe80::1', '::1');
    }

    public static function getSubscribedEvents()
    {
        return array(
            Symfony_Component_HttpKernel_KernelEvents::REQUEST => array(array('onKernelRequest', 48)),
        );
    }
}
