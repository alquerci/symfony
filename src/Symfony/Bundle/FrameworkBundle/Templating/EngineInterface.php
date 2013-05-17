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
 * EngineInterface is the interface each engine must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface Symfony_Bundle_FrameworkBundle_Templating_EngineInterface extends Symfony_Component_Templating_EngineInterface
{
    /**
     * Renders a view and returns a Response.
     *
     * @param string   $view       The view name
     * @param array    $parameters An array of parameters to pass to the view
     * @param Symfony_Component_HttpFoundation_Response $response   A Response instance
     *
     * @return Symfony_Component_HttpFoundation_Response A Response instance
     */
    public function renderResponse($view, array $parameters = array(), Symfony_Component_HttpFoundation_Response $response = null);
}
