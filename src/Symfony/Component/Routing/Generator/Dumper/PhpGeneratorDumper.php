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
 * PhpGeneratorDumper creates a PHP class able to generate URLs for a given set of routes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 *
 * @api
 */
class Symfony_Component_Routing_Generator_Dumper_PhpGeneratorDumper extends Symfony_Component_Routing_Generator_Dumper_GeneratorDumper
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
     * @return string A PHP class representing the generator class
     *
     * @api
     */
    public function dump(array $options = array())
    {
        $options = array_merge(array(
            'class'      => 'ProjectUrlGenerator',
            'base_class' => 'Symfony_Component_Routing_Generator_UrlGenerator',
        ), $options);

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
    static private \$declaredRoutes = {$this->generateDeclaredRoutes()};

    /**
     * Constructor.
     */
    public function __construct(Symfony_Component_Routing_RequestContext \$context, Psr_Log_LoggerInterface \$logger = null)
    {
        \$this->context = \$context;
        \$this->logger = \$logger;
    }

{$this->generateGenerateMethod($options['class'])}
}

EOF;
    }

    /**
     * Generates PHP code representing an array of defined routes
     * together with the routes properties (e.g. requirements).
     *
     * @return string PHP code
     */
    private function generateDeclaredRoutes()
    {
        $routes = "array(\n";
        foreach ($this->getRoutes()->all() as $name => $route) {
            $compiledRoute = $route->compile();

            $properties = array();
            $properties[] = $compiledRoute->getVariables();
            $properties[] = $route->getDefaults();
            $properties[] = $route->getRequirements();
            $properties[] = $compiledRoute->getTokens();
            $properties[] = $compiledRoute->getHostTokens();

            $routes .= sprintf("        '%s' => %s,\n", $name, str_replace("\n", '', var_export($properties, true)));
        }
        $routes .= '    )';

        return $routes;
    }

    /**
     * Generates PHP code representing the `generate` method that implements the UrlGeneratorInterface.
     *
     * @param string $class The class name
     *
     * @return string PHP code
     */
    private function generateGenerateMethod($class)
    {
        return <<<EOF
    public function generate(\$name, \$parameters = array(), \$referenceType = {$class}::ABSOLUTE_PATH)
    {
        if (!isset(self::\$declaredRoutes[\$name])) {
            throw new Symfony_Component_Routing_Exception_RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', \$name));
        }

        list(\$variables, \$defaults, \$requirements, \$tokens, \$hostTokens) = self::\$declaredRoutes[\$name];

        return \$this->doGenerate(\$variables, \$defaults, \$requirements, \$tokens, \$parameters, \$name, \$referenceType, \$hostTokens);
    }
EOF;
    }
}
