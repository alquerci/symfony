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
 * This engine knows how to render Symfony templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_PhpEngine extends Symfony_Component_Templating_PhpEngine implements Symfony_Bundle_FrameworkBundle_Templating_EngineInterface
{
    protected $container;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Templating_TemplateNameParserInterface $parser    A TemplateNameParserInterface instance
     * @param Symfony_Component_DependencyInjection_ContainerInterface          $container The DI container
     * @param Symfony_Component_Templating_Loader_LoaderInterface             $loader    A loader instance
     * @param Symfony_Bundle_FrameworkBundle_Templating_GlobalVariables|null        $globals   A GlobalVariables instance or null
     */
    public function __construct(Symfony_Component_Templating_TemplateNameParserInterface $parser, Symfony_Component_DependencyInjection_ContainerInterface $container, Symfony_Component_Templating_Loader_LoaderInterface $loader, Symfony_Bundle_FrameworkBundle_Templating_GlobalVariables $globals = null)
    {
        $this->container = $container;

        parent::__construct($parser, $loader);

        if (null !== $globals) {
            $this->addGlobal('app', $globals);
        }
    }

    /**
     * @throws InvalidArgumentException When the helper is not defined
     */
    public function get($name)
    {
        if (!isset($this->helpers[$name])) {
            throw new InvalidArgumentException(sprintf('The helper "%s" is not defined.', $name));
        }

        if (is_string($this->helpers[$name])) {
            $this->helpers[$name] = $this->container->get($this->helpers[$name]);
            $this->helpers[$name]->setCharset($this->charset);
        }

        return $this->helpers[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setHelpers(array $helpers)
    {
        $this->helpers = $helpers;
    }

    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Symfony_Component_HttpFoundation_Response $response   A Response instance
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Symfony_Component_HttpFoundation_Response $response = null)
    {
        if (null === $response) {
            $response = new Symfony_Component_HttpFoundation_Response();
        }

        $response->setContent($this->render($view, $parameters));

        return $response;
    }
}
