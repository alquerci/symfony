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
 * PhpMatcherDumper creates a PHP class able to match URLs for a given set of routes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 * @author Arnaud Le Blanc <arnaud.lb@gmail.com>
 */
class Symfony_Component_Routing_Matcher_Dumper_PhpMatcherDumper extends Symfony_Component_Routing_Matcher_Dumper_MatcherDumper
{
    /**
     * Dumps a set of routes to a PHP class.
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     *
     * @param array $options An array of options
     *
     * @return string A PHP class representing the matcher class
     */
    public function dump(array $options = array())
    {
        $options = array_replace(array(
            'class'      => 'ProjectUrlMatcher',
            'base_class' => 'Symfony_Component_Routing_Matcher_UrlMatcher',
        ), $options);

        // trailing slash support is only enabled if we know how to redirect the user
        $interfaces = class_implements($options['base_class']);
        $supportsRedirections = isset($interfaces['Symfony_Component_Routing_Matcher_RedirectableUrlMatcherInterface']);

        return <<<EOF
<?php

/**
 * {$options['class']}
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class {$options['class']} extends {$options['base_class']}
{
    /**
     * Constructor.
     */
    public function __construct(Symfony_Component_Routing_RequestContext \$context)
    {
        \$this->context = \$context;
    }

{$this->generateMatchMethod($supportsRedirections)}
}

EOF;
    }

    /**
     * Generates the code for the match method implementing UrlMatcherInterface.
     *
     * @param Boolean $supportsRedirections Whether redirections are supported by the base class
     *
     * @return string Match method as PHP code
     */
    private function generateMatchMethod($supportsRedirections)
    {
        $code = rtrim($this->compileRoutes($this->getRoutes(), $supportsRedirections), "\n");

        return <<<EOF
    public function match(\$pathinfo)
    {
        \$allow = array();
        \$pathinfo = rawurldecode(\$pathinfo);

$code

        throw 0 < count(\$allow) ? new Symfony_Component_Routing_Exception_MethodNotAllowedException(array_unique(\$allow)) : new Symfony_Component_Routing_Exception_ResourceNotFoundException();
    }
EOF;
    }

    /**
     * Generates PHP code to match a RouteCollection with all its routes.
     *
     * @param Symfony_Component_Routing_RouteCollection $routes               A RouteCollection instance
     * @param Boolean         $supportsRedirections Whether redirections are supported by the base class
     *
     * @return string PHP code
     */
    private function compileRoutes(Symfony_Component_Routing_RouteCollection $routes, $supportsRedirections)
    {
        $fetchedHost = false;

        $groups = $this->groupRoutesByHostRegex($routes);
        $code = '';

        foreach ($groups as $collection) {
            if (null !== $regex = $collection->getAttribute('host_regex')) {
                if (!$fetchedHost) {
                    $code .= "        \$host = \$this->context->getHost();\n\n";
                    $fetchedHost = true;
                }

                $code .= sprintf("        if (preg_match(%s, \$host, \$hostMatches)) {\n", var_export($regex, true));
            }

            $tree = $this->buildPrefixTree($collection);
            $groupCode = $this->compilePrefixRoutes($tree, $supportsRedirections);

            if (null !== $regex) {
                // apply extra indention at each line (except empty ones)
                $groupCode = preg_replace('/^.{2,}$/m', '    $0', $groupCode);
                $code .= $groupCode;
                $code .= "        }\n\n";
            } else {
                $code .= $groupCode;
            }
        }

        return $code;
    }

    /**
     * Generates PHP code recursively to match a tree of routes
     *
     * @param Symfony_Component_Routing_Matcher_Dumper_DumperPrefixCollection $collection           A DumperPrefixCollection instance
     * @param Boolean                $supportsRedirections Whether redirections are supported by the base class
     * @param string                 $parentPrefix         Prefix of the parent collection
     *
     * @return string PHP code
     */
    private function compilePrefixRoutes(Symfony_Component_Routing_Matcher_Dumper_DumperPrefixCollection $collection, $supportsRedirections, $parentPrefix = '')
    {
        $code = '';
        $prefix = $collection->getPrefix();
        $optimizable = 1 < strlen($prefix) && 1 < count($collection->all());
        $optimizedPrefix = $parentPrefix;

        if ($optimizable) {
            $optimizedPrefix = $prefix;

            $code .= sprintf("    if (0 === strpos(\$pathinfo, %s)) {\n", var_export($prefix, true));
        }

        foreach ($collection as $route) {
            if ($route instanceof Symfony_Component_Routing_Matcher_Dumper_DumperCollection) {
                $code .= $this->compilePrefixRoutes($route, $supportsRedirections, $optimizedPrefix);
            } else {
                $code .= $this->compileRoute($route->getRoute(), $route->getName(), $supportsRedirections, $optimizedPrefix)."\n";
            }
        }

        if ($optimizable) {
            $code .= "    }\n\n";
            // apply extra indention at each line (except empty ones)
            $code = preg_replace('/^.{2,}$/m', '    $0', $code);
        }

        return $code;
    }

    /**
     * Compiles a single Route to PHP code used to match it against the path info.
     *
     * @param Symfony_Component_Routing_Route       $route                A Route instance
     * @param string      $name                 The name of the Route
     * @param Boolean     $supportsRedirections Whether redirections are supported by the base class
     * @param string|null $parentPrefix         The prefix of the parent collection used to optimize the code
     *
     * @return string PHP code
     *
     * @throws LogicException
     */
    private function compileRoute(Symfony_Component_Routing_Route $route, $name, $supportsRedirections, $parentPrefix = null)
    {
        $code = '';
        $compiledRoute = $route->compile();
        $conditions = array();
        $hasTrailingSlash = false;
        $matches = false;
        $hostMatches = false;
        $methods = array();

        if ($req = $route->getRequirement('_method')) {
            $methods = explode('|', strtoupper($req));
            // GET and HEAD are equivalent
            if (in_array('GET', $methods) && !in_array('HEAD', $methods)) {
                $methods[] = 'HEAD';
            }
        }

        $supportsTrailingSlash = $supportsRedirections && (!$methods || in_array('HEAD', $methods));

        if (!count($compiledRoute->getPathVariables()) && false !== preg_match('#^(.)\^(?P<url>.*?)\$\1#', $compiledRoute->getRegex(), $m)) {
            if ($supportsTrailingSlash && substr($m['url'], -1) === '/') {
                $conditions[] = sprintf("rtrim(\$pathinfo, '/') === %s", var_export(rtrim(str_replace('\\', '', $m['url']), '/'), true));
                $hasTrailingSlash = true;
            } else {
                $conditions[] = sprintf("\$pathinfo === %s", var_export(str_replace('\\', '', $m['url']), true));
            }
        } else {
            if ($compiledRoute->getStaticPrefix() && $compiledRoute->getStaticPrefix() !== $parentPrefix) {
                $conditions[] = sprintf("0 === strpos(\$pathinfo, %s)", var_export($compiledRoute->getStaticPrefix(), true));
            }

            $regex = $compiledRoute->getRegex();
            if ($supportsTrailingSlash && $pos = strpos($regex, '/$')) {
                $regex = substr($regex, 0, $pos).'/?$'.substr($regex, $pos + 2);
                $hasTrailingSlash = true;
            }
            $conditions[] = sprintf("preg_match(%s, \$pathinfo, \$matches)", var_export($regex, true));

            $matches = true;
        }

        if ($compiledRoute->getHostVariables()) {
            $hostMatches = true;
        }

        $conditions = implode(' && ', $conditions);

        $code .= <<<EOF
        // $name
        if ($conditions) {

EOF;

        if ($methods) {
            if (1 === count($methods)) {
                $code .= <<<EOF
            if (\$this->context->getMethod() != '$methods[0]') {
                \$allow[] = '$methods[0]';
            } else {

EOF;
            } else {
                $methods = implode("', '", $methods);
                $code .= <<<EOF
            if (!in_array(\$this->context->getMethod(), array('$methods'))) {
                \$allow = array_merge(\$allow, array('$methods'));
            } else {

EOF;
            }
        }

        if ($hasTrailingSlash) {
            $code .= <<<EOF
            if (substr(\$pathinfo, -1) !== '/') {
                return \$this->redirect(\$pathinfo.'/', '$name');
            }


EOF;
        }

        if ($scheme = $route->getRequirement('_scheme')) {
            if (!$supportsRedirections) {
                throw new LogicException('The "_scheme" requirement is only supported for URL matchers that implement RedirectableUrlMatcherInterface.');
            }

            $code .= <<<EOF
            if (\$this->context->getScheme() !== '$scheme') {
                return \$this->redirect(\$pathinfo, '$name', '$scheme');
            }


EOF;
        }

        // optimize parameters array
        if ($matches || $hostMatches) {
            $vars = array();
            if ($hostMatches) {
                $vars[] = '$hostMatches';
            }
            if ($matches) {
                $vars[] = '$matches';
            }
            $vars[] = "array('_route' => '$name')";

            $code .= sprintf("            return \$this->mergeDefaults(array_replace(%s), %s);\n"
                , implode(', ', $vars), str_replace("\n", '', var_export($route->getDefaults(), true)));

        } elseif ($route->getDefaults()) {
            $code .= sprintf("            return %s;\n", str_replace("\n", '', var_export(array_replace($route->getDefaults(), array('_route' => $name)), true)));
        } else {
            $code .= sprintf("            return array('_route' => '%s');\n", $name);
        }

        if ($methods) {
            $code .= "            }\n"; // goto statement was removed
        }

        $code .= "        }\n";

        return $code;
    }

    /**
     * Groups consecutive routes having the same host regex.
     *
     * The result is a collection of collections of routes having the same host regex.
     *
     * @param Symfony_Component_Routing_RouteCollection $routes A flat RouteCollection
     *
     * @return Symfony_Component_Routing_Matcher_Dumper_DumperCollection A collection with routes grouped by host regex in sub-collections
     */
    private function groupRoutesByHostRegex(Symfony_Component_Routing_RouteCollection $routes)
    {
        $groups = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();

        $currentGroup = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();
        $currentGroup->setAttribute('host_regex', null);
        $groups->add($currentGroup);

        foreach ($routes as $name => $route) {
            $hostRegex = $route->compile()->getHostRegex();
            if ($currentGroup->getAttribute('host_regex') !== $hostRegex) {
                $currentGroup = new Symfony_Component_Routing_Matcher_Dumper_DumperCollection();
                $currentGroup->setAttribute('host_regex', $hostRegex);
                $groups->add($currentGroup);
            }
            $currentGroup->add(new Symfony_Component_Routing_Matcher_Dumper_DumperRoute($name, $route));
        }

        return $groups;
    }

    /**
     * Organizes the routes into a prefix tree.
     *
     * Routes order is preserved such that traversing the tree will traverse the
     * routes in the origin order.
     *
     * @param Symfony_Component_Routing_Matcher_Dumper_DumperCollection $collection A collection of routes
     *
     * @return Symfony_Component_Routing_Matcher_Dumper_DumperPrefixCollection
     */
    private function buildPrefixTree(Symfony_Component_Routing_Matcher_Dumper_DumperCollection $collection)
    {
        $tree = new Symfony_Component_Routing_Matcher_Dumper_DumperPrefixCollection();
        $current = $tree;

        foreach ($collection as $route) {
            $current = $current->addPrefixRoute($route);
        }

        $tree->mergeSlashNodes();

        return $tree;
    }
}
