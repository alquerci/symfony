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
 * Adds the possibility to generate a fragment URI for a given Controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_HttpKernel_Fragment_RoutableFragmentRenderer implements Symfony_Component_HttpKernel_Fragment_FragmentRendererInterface
{
    private $fragmentPath = '/_fragment';

    /**
     * Sets the fragment path that triggers the fragment listener.
     *
     * @param string $path The path
     *
     * @see Symfony_Component_HttpKernel_EventListener_FragmentListener
     */
    public function setFragmentPath($path)
    {
        $this->fragmentPath = $path;
    }

    /**
     * Generates a fragment URI for a given controller.
     *
     * @param Symfony_Component_HttpKernel_Controller_ControllerReference  $reference A ControllerReference instance
     * @param Symfony_Component_HttpFoundation_Request              $request    A Request instance
     *
     * @return string A fragment URI
     */
    protected function generateFragmentUri(Symfony_Component_HttpKernel_Controller_ControllerReference $reference, Symfony_Component_HttpFoundation_Request $request)
    {
        if (!isset($reference->attributes['_format'])) {
            $reference->attributes['_format'] = $request->getRequestFormat();
        }

        $reference->attributes['_controller'] = $reference->controller;

        $reference->query['_path'] = http_build_query($reference->attributes, '', '&');

        return $request->getUriForPath($this->fragmentPath.'?'.http_build_query($reference->query, '', '&'));
    }
}
