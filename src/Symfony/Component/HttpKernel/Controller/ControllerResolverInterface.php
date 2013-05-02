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
 * A ControllerResolverInterface implementation knows how to determine the
 * controller to execute based on a Request object.
 *
 * It can also determine the arguments to pass to the Controller.
 *
 * A Controller can be any valid PHP callable.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
interface Symfony_Component_HttpKernel_Controller_ControllerResolverInterface
{
    /**
     * Returns the Controller instance associated with a Request.
     *
     * As several resolvers can exist for a single application, a resolver must
     * return false when it is not able to determine the controller.
     *
     * The resolver must only throw an exception when it should be able to load
     * controller but cannot because of some errors made by the developer.
     *
     * @param Symfony_Component_HttpFoundation_Request $request A Request instance
     *
     * @return mixed|Boolean A PHP callable representing the Controller,
     *                       or false if this resolver is not able to determine the controller
     *
     * @throws InvalidArgumentException|LogicException If the controller can't be found
     *
     * @api
     */
    public function getController(Symfony_Component_HttpFoundation_Request $request);

    /**
     * Returns the arguments to pass to the controller.
     *
     * @param Symfony_Component_HttpFoundation_Request $request    A Request instance
     * @param mixed   $controller A PHP callable
     *
     * @return array An array of arguments to pass to the controller
     *
     * @throws RuntimeException When value for argument given is not provided
     *
     * @api
     */
    public function getArguments(Symfony_Component_HttpFoundation_Request $request, $controller);
}
