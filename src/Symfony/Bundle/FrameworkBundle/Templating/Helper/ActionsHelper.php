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
 * ActionsHelper manages action inclusions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Helper_ActionsHelper extends Symfony_Component_Templating_Helper_Helper
{
    private $handler;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_Fragment_FragmentHandler $handler A FragmentHandler instance
     */
    public function __construct(Symfony_Component_HttpKernel_Fragment_FragmentHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Returns the fragment content for a given URI.
     *
     * @param string $uri     A URI
     * @param array  $options An array of options
     *
     * @return string The fragment content
     *
     * @see Symfony_Component_HttpKernel_Fragment_FragmentHandler::render()
     */
    public function render($uri, array $options = array())
    {
        $options = $this->handler->fixOptions($options);

        $strategy = isset($options['strategy']) ? $options['strategy'] : 'inline';
        unset($options['strategy']);

        return $this->handler->render($uri, $strategy, $options);
    }

    public function controller($controller, $attributes = array(), $query = array())
    {
        return new Symfony_Component_HttpKernel_Controller_ControllerReference($controller, $attributes, $query);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'actions';
    }
}
