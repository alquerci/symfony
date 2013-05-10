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
 * ControllerResolver.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Controller_ControllerResolver extends Symfony_Component_HttpKernel_Controller_ControllerResolver
{
    protected $container;
    protected $parser;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface   $container A ContainerInterface instance
     * @param Symfony_Bundle_FrameworkBundle_Controller_ControllerNameParser $parser    A ControllerNameParser instance
     * @param Psr_Log_LoggerInterface      $logger    A LoggerInterface instance
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container, Symfony_Bundle_FrameworkBundle_Controller_ControllerNameParser $parser, Psr_Log_LoggerInterface $logger = null)
    {
        $this->container = $container;
        $this->parser = $parser;

        parent::__construct($logger);
    }

    /**
     * Returns a callable for the given controller.
     *
     * @param string $controller A Controller string
     *
     * @return mixed A PHP callable
     *
     * @throws LogicException When the name could not be parsed
     * @throws InvalidArgumentException When the controller class does not exist
     */
    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                // controller in the a:b:c notation then
                $controller = $this->parser->parse($controller);
            } elseif (1 == $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller, 2);

                return array($this->container->get($service), $method);
            } else {
                throw new LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $controller = new $class();
        if ($controller instanceof Symfony_Component_DependencyInjection_ContainerAwareInterface) {
            $controller->setContainer($this->container);
        }

        return array($controller, $method);
    }
}
