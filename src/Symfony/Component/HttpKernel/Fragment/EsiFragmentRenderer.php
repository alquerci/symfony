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
 * Implements the ESI rendering strategy.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_Fragment_EsiFragmentRenderer extends Symfony_Component_HttpKernel_Fragment_RoutableFragmentRenderer
{
    private $esi;
    private $inlineStrategy;

    /**
     * Constructor.
     *
     * The "fallback" strategy when ESI is not available should always be an
     * instance of InlineFragmentRenderer.
     *
     * @param Symfony_Component_HttpKernel_HttpCache_Esi                    $esi            An Esi instance
     * @param Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer $inlineStrategy The inline strategy to use when ESI is not supported
     */
    public function __construct(Symfony_Component_HttpKernel_HttpCache_Esi $esi, Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer $inlineStrategy)
    {
        $this->esi = $esi;
        $this->inlineStrategy = $inlineStrategy;
    }

    /**
     * {@inheritdoc}
     *
     * Note that if the current Request has no ESI capability, this method
     * falls back to use the inline rendering strategy.
     *
     * Additional available options:
     *
     *  * alt: an alternative URI to render in case of an error
     *  * comment: a comment to add when returning an esi:include tag
     *
     * @see Symfony\Component\HttpKernel\HttpCache\ESI
     */
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array())
    {
        if (!$this->esi->hasSurrogateEsiCapability($request)) {
            return $this->inlineStrategy->render($uri, $request, $options);
        }

        if ($uri instanceof Symfony_Component_HttpKernel_Controller_ControllerReference) {
            $uri = $this->generateFragmentUri($uri, $request);
        }

        $alt = isset($options['alt']) ? $options['alt'] : null;
        if ($alt instanceof Symfony_Component_HttpKernel_Controller_ControllerReference) {
            $alt = $this->generateFragmentUri($alt, $request);
        }

        $tag = $this->esi->renderIncludeTag($uri, $alt, isset($options['ignore_errors']) ? $options['ignore_errors'] : false, isset($options['comment']) ? $options['comment'] : '');

        return new Symfony_Component_HttpFoundation_Response($tag);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'esi';
    }
}
