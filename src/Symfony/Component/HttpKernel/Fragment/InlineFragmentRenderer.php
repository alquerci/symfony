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
 * Implements the inline rendering strategy where the Request is rendered by the current HTTP kernel.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_HttpKernel_Fragment_InlineFragmentRenderer extends Symfony_Component_HttpKernel_Fragment_RoutableFragmentRenderer
{
    private $kernel;

    /**
     * Constructor.
     *
     * @param Symfony_Component_HttpKernel_HttpKernelInterface $kernel A HttpKernelInterface instance
     */
    public function __construct(Symfony_Component_HttpKernel_HttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * {@inheritdoc}
     *
     * Additional available options:
     *
     *  * alt: an alternative URI to render in case of an error
     */
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array())
    {
        $reference = null;
        if ($uri instanceof Symfony_Component_HttpKernel_Controller_ControllerReference) {
            $reference = $uri;
            $uri = $this->generateFragmentUri($uri, $request);
        }

        $subRequest = $this->createSubRequest($uri, $request);

        // override Request attributes as they can be objects (which are not supported by the generated URI)
        if (null !== $reference) {
            $subRequest->attributes->add($reference->attributes);
        }

        $level = ob_get_level();
        try {
            return $this->kernel->handle($subRequest, Symfony_Component_HttpKernel_HttpKernelInterface::SUB_REQUEST, false);
        } catch (Exception $e) {
            // let's clean up the output buffers that were created by the sub-request
            while (ob_get_level() > $level) {
                ob_get_clean();
            }

            if (isset($options['alt'])) {
                $alt = $options['alt'];
                unset($options['alt']);

                return $this->render($alt, $request, $options);
            }

            if (!isset($options['ignore_errors']) || !$options['ignore_errors']) {
                throw $e;
            }

            return new Symfony_Component_HttpFoundation_Response();
        }
    }

    protected function createSubRequest($uri, Symfony_Component_HttpFoundation_Request $request)
    {
        $cookies = $request->cookies->all();
        $server = $request->server->all();

        // the sub-request is internal
        $server['REMOTE_ADDR'] = '127.0.0.1';

        $subRequest = $request->create($uri, 'get', array(), $cookies, array(), $server);
        if ($session = $request->getSession()) {
            $subRequest->setSession($session);
        }

        return $subRequest;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'inline';
    }
}
