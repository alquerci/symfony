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
 * Interface implemented by all rendering strategies.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @see Symfony_Component_HttpKernel_FragmentRenderer
 */
interface Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface
{
    /**
     * Renders a URI and returns the Response content.
     *
     * @param string|Symfony_Component_HttpKernel_Controller_ControllerReference $uri     A URI as a string or a ControllerReference instance
     * @param Symfony_Component_HttpFoundation_Request                    $request A Request instance
     * @param array                      $options An array of options
     *
     * @return Response A Response instance
     */
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array());

    /**
     * Gets the name of the strategy.
     *
     * @return string The strategy name
     */
    public function getName();
}
