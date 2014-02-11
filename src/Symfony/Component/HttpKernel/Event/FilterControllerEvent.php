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
 * Allows filtering of a controller callable
 *
 * You can call getController() to retrieve the current controller. With
 * setController() you can set a new controller that is used in the processing
 * of the request.
 *
 * Controllers should be callables.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_HttpKernel_Event_FilterControllerEvent extends Symfony_Component_HttpKernel_Event_KernelEvent
{
    /**
     * The current controller
     * @var callable
     */
    private $controller;

    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel, $controller, Symfony_Component_HttpFoundation_Request $request, $requestType)
    {
        parent::__construct($kernel, $request, $requestType);

        $this->setController($controller);
    }

    /**
     * Returns the current controller
     *
     * @return callable
     *
     * @api
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets a new controller
     *
     * @param callable $controller
     *
     * @throws LogicException
     *
     * @api
     */
    public function setController($controller)
    {
        // controller must be a callable
        if (!(is_callable($controller) || (is_object($controller) && method_exists($controller, '__invoke')))) {
            throw new LogicException(sprintf('The controller must be a callable (%s given).', $this->varToString($controller)));
        }

        $this->controller = $controller;
    }

    private function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf("Array(%s)", implode(', ', $a));
        }

        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'false';
        }

        if (true === $var) {
            return 'true';
        }

        return (string) $var;
    }
}
