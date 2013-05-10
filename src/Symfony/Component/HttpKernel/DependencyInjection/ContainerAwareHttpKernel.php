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
 * This HttpKernel is used to manage scope changes of the DI container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_HttpKernel_DependencyInjection_ContainerAwareHttpKernel extends Symfony_Component_HttpKernel_HttpKernel
{
    protected $container;

    /**
     * Constructor.
     *
     * @param Symfony_Component_EventDispatcher_EventDispatcherInterface    $dispatcher         An EventDispatcherInterface instance
     * @param Symfony_Component_DependencyInjection_ContainerInterface          $container          A ContainerInterface instance
     * @param Symfony_Component_HttpKernel_Controller_ControllerResolverInterface $controllerResolver A ControllerResolverInterface instance
     */
    public function __construct(Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher, Symfony_Component_DependencyInjection_ContainerInterface $container, Symfony_Component_HttpKernel_Controller_ControllerResolverInterface $controllerResolver)
    {
        parent::__construct($dispatcher, $controllerResolver);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Symfony_Component_HttpFoundation_Request $request, $type = Symfony_Component_HttpKernel_HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $request->headers->set('X-Php-Ob-Level', ob_get_level());

        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        try {
            $response = parent::handle($request, $type, $catch);
        } catch (Exception $e) {
            $this->container->leaveScope('request');

            throw $e;
        }

        $this->container->leaveScope('request');

        return $response;
    }
}
