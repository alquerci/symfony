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
 * Encapsulates the logic needed to create sub-requests, redirect the user, and match URLs.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_HttpUtils
{
    private $urlGenerator;
    private $urlMatcher;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Routing_Generator_UrlGeneratorInterface                       $urlGenerator A UrlGeneratorInterface instance
     * @param Symfony_Component_Routing_Matcher_UrlMatcherInterface|Symfony_Component_Routing_Matcher_RequestMatcherInterface $urlMatcher   The Url or Request matcher
     */
    public function __construct(Symfony_Component_Routing_Generator_UrlGeneratorInterface $urlGenerator = null, $urlMatcher = null)
    {
        $this->urlGenerator = $urlGenerator;
        if ($urlMatcher !== null && !$urlMatcher instanceof Symfony_Component_Routing_Matcher_UrlMatcherInterface && !$urlMatcher instanceof Symfony_Component_Routing_Matcher_RequestMatcherInterface) {
            throw new InvalidArgumentException('Matcher must either implement UrlMatcherInterface or RequestMatcherInterface.');
        }
        $this->urlMatcher = $urlMatcher;
    }

    /**
     * Creates a redirect Response.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     * @param string  $path    A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     * @param integer $status  The status code
     *
     * @return Response A RedirectResponse instance
     */
    public function createRedirectResponse(Symfony_Component_HttpFoundation_Request $request, $path, $status = 302)
    {
        return new Symfony_Component_HttpFoundation_RedirectResponse($this->generateUri($request, $path), $status);
    }

    /**
     * Creates a Request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request The current Request instance
     * @param string  $path    A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     *
     * @return Symfony_Component_HttpFoundation_Request A Request instance
     */
    public function createRequest(Symfony_Component_HttpFoundation_Request $request, $path)
    {
        $newRequest = $request->create($this->generateUri($request, $path), 'get', array(), $request->cookies->all(), array(), $request->server->all());
        if ($request->hasSession()) {
            $newRequest->setSession($request->getSession());
        }

        if ($request->attributes->has(Symfony_Component_Security_Core_SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $newRequest->attributes->set(Symfony_Component_Security_Core_SecurityContextInterface::AUTHENTICATION_ERROR, $request->attributes->get(Symfony_Component_Security_Core_SecurityContextInterface::AUTHENTICATION_ERROR));
        }
        if ($request->attributes->has(Symfony_Component_Security_Core_SecurityContextInterface::ACCESS_DENIED_ERROR)) {
            $newRequest->attributes->set(Symfony_Component_Security_Core_SecurityContextInterface::ACCESS_DENIED_ERROR, $request->attributes->get(Symfony_Component_Security_Core_SecurityContextInterface::ACCESS_DENIED_ERROR));
        }
        if ($request->attributes->has(Symfony_Component_Security_Core_SecurityContextInterface::LAST_USERNAME)) {
            $newRequest->attributes->set(Symfony_Component_Security_Core_SecurityContextInterface::LAST_USERNAME, $request->attributes->get(Symfony_Component_Security_Core_SecurityContextInterface::LAST_USERNAME));
        }

        return $newRequest;
    }

    /**
     * Checks that a given path matches the Request.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     * @param string  $path    A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     *
     * @return Boolean true if the path is the same as the one from the Request, false otherwise
     */
    public function checkRequestPath(Symfony_Component_HttpFoundation_Request $request, $path)
    {
        if ('/' !== $path[0]) {
            try {
                // matching a request is more powerful than matching a URL path + context, so try that first
                if ($this->urlMatcher instanceof Symfony_Component_Routing_Matcher_RequestMatcherInterface) {
                    $parameters = $this->urlMatcher->matchRequest($request);
                } else {
                    $parameters = $this->urlMatcher->match($request->getPathInfo());
                }

                return $path === $parameters['_route'];
            } catch (Symfony_Component_Routing_Exception_MethodNotAllowedException $e) {
                return false;
            } catch (Symfony_Component_Routing_Exception_ResourceNotFoundException $e) {
                return false;
            }
        }

        return $path === rawurldecode($request->getPathInfo());
    }

    /**
     * Generates a URI, based on the given path or absolute URL.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     * @param string $path A path (an absolute path (/foo), an absolute URL (http://...), or a route name (foo))
     *
     * @return string An absolute URL
     */
    public function generateUri($request, $path)
    {
        if (0 === strpos($path, 'http') || !$path) {
            return $path;
        }

        if ('/' === $path[0]) {
            return $request->getUriForPath($path);
        }

        if (null === $this->urlGenerator) {
            throw new LogicException('You must provide a UrlGeneratorInterface instance to be able to use routes.');
        }

        $url = $this->urlGenerator->generate($path, $request->attributes->all(), Symfony_Component_Routing_Generator_UrlGeneratorInterface::ABSOLUTE_URL);

        // unnecessary query string parameters must be removed from url
        // (ie. query parameters that are presents in $attributes)
        // fortunately, they all are, so we have to remove entire query string
        $position = strpos($url, '?');
        if (false !== $position) {
            $url = substr($url, 0, $position);
        }

        return $url;
    }
}
