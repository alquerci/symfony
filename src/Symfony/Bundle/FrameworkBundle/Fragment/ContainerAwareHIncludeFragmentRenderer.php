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
 * Implements the Hinclude rendering strategy.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Fragment_ContainerAwareHIncludeFragmentRenderer extends Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer
{
    private $container;

    /**
     * {@inheritdoc}
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container, Symfony_Component_HttpKernel_UriSigner $signer = null, $globalDefaultTemplate = null)
    {
        $this->container = $container;

        parent::__construct(null, $signer, $globalDefaultTemplate);
    }

    /**
     * {@inheritdoc}
     */
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array())
    {
        // setting the templating cannot be done in the constructor
        // as it would lead to an infinite recursion in the service container
        if (!$this->hasTemplating()) {
            $this->setTemplating($this->container->get('templating'));
        }

        return parent::render($uri, $request, $options);
    }
}
