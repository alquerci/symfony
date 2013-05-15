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
class Symfony_Component_HttpKernel_Fragment_HIncludeFragmentRenderer extends Symfony_Component_HttpKernel_Fragment_RoutableFragmentRenderer
{
    private $globalDefaultTemplate;
    private $signer;
    private $templating;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Templating_EngineInterface|Twig_Environment $templating            An EngineInterface or a \Twig_Environment instance
     * @param Symfony_Component_HttpKernel_UriSigner                         $signer                A UriSigner instance
     * @param string                            $globalDefaultTemplate The global default content (it can be a template name or the content)
     */
    public function __construct($templating = null, Symfony_Component_HttpKernel_UriSigner $signer = null, $globalDefaultTemplate = null)
    {
        $this->setTemplating($templating);
        $this->globalDefaultTemplate = $globalDefaultTemplate;
        $this->signer = $signer;
    }

    /**
     * Sets the templating engine to use to render the default content.
     *
     * @param Symfony_Component_Templating_EngineInterface|Twig_Environment|null $templating An EngineInterface or a \Twig_Environment instance
     */
    public function setTemplating($templating)
    {
        if (null !== $templating && !$templating instanceof Symfony_Component_Templating_EngineInterface && !$templating instanceof Twig_Environment) {
            throw new InvalidArgumentException('The hinclude rendering strategy needs an instance of \Twig_Environment or Symfony\Component\Templating\EngineInterface');
        }

        $this->templating = $templating;
    }

    /**
     * Checks if a templating engine has been set.
     *
     * @return Boolean true if the templating engine has been set, false otherwise
     */
    public function hasTemplating()
    {
        return null !== $this->templating;
    }

    /**
     * {@inheritdoc}
     *
     * Additional available options:
     *
     *  * default: The default content (it can be a template name or the content)
     */
    public function render($uri, Symfony_Component_HttpFoundation_Request $request, array $options = array())
    {
        if ($uri instanceof Symfony_Component_HttpKernel_Controller_ControllerReference) {
            if (null === $this->signer) {
                throw new LogicException('You must use a proper URI when using the Hinclude rendering strategy or set a URL signer.');
            }

            $uri = $this->signer->sign($this->generateFragmentUri($uri, $request));
        }

        // We need to replace ampersands in the URI with the encoded form in order to return valid html/xml content.
        $uri = str_replace('&', '&amp;', $uri);

        $template = isset($options['default']) ? $options['default'] : $this->globalDefaultTemplate;
        if (null !== $this->templating && $template && $this->templateExists($template)) {
            $content = $this->templating->render($template);
        } else {
            $content = $template;
        }

        return new Symfony_Component_HttpFoundation_Response(sprintf('<hx:include src="%s">%s</hx:include>', $uri, $content));
    }

    private function templateExists($template)
    {
        if ($this->templating instanceof Symfony_Component_Templating_EngineInterface) {
            return $this->templating->exists($template);
        }

        $loader = $this->templating->getLoader();
        if ($loader instanceof Twig_ExistsLoaderInterface) {
            return $loader->exists($template);
        }

        try {
            $loader->getSource($template);

            return true;
        } catch (Twig_Error_Loader $e) {
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'hinclude';
    }
}
