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
 * RouterHelper manages links between pages in a template context.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Helper_RouterHelper extends Symfony_Component_Templating_Helper_Helper
{
    protected $generator;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Routing_Generator_UrlGeneratorInterface $router A Router instance
     */
    public function __construct(Symfony_Component_Routing_Generator_UrlGeneratorInterface $router)
    {
        $this->generator = $router;
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string         $name          The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see Symfony_Component_Routing_Generator_UrlGeneratorInterface
     */
    public function generate($name, $parameters = array(), $referenceType = Symfony_Component_Routing_Generator_UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->generator->generate($name, $parameters, $referenceType);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'router';
    }
}
