<?php

/**
 * ProjectUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class ProjectUrlMatcher extends Symfony_Component_Routing_Matcher_UrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(Symfony_Component_Routing_RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);

        if (0 === strpos($pathinfo, '/rootprefix')) {
            // static
            if ($pathinfo === '/rootprefix/test') {
                return array('_route' => 'static');
            }

            // dynamic
            if (preg_match('#^/rootprefix/(?P<var>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => 'dynamic')), array ());
            }

        }

        throw 0 < count($allow) ? new Symfony_Component_Routing_Exception_MethodNotAllowedException(array_unique($allow)) : new Symfony_Component_Routing_Exception_ResourceNotFoundException();
    }
}
