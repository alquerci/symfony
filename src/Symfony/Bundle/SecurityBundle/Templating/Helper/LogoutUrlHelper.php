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
 * LogoutUrlHelper provides generator functions for the logout URL.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class Symfony_Bundle_SecurityBundle_Templating_Helper_LogoutUrlHelper extends Symfony_Component_Templating_Helper_Helper
{
    private $container;
    private $listeners;
    private $router;

    /**
     * Constructor.
     *
     * @param Symfony_Component_DependencyInjection_ContainerInterface    $container A ContainerInterface instance
     * @param Symfony_Component_Routing_Generator_UrlGeneratorInterface $router    A Router instance
     */
    public function __construct(Symfony_Component_DependencyInjection_ContainerInterface $container, Symfony_Component_Routing_Generator_UrlGeneratorInterface $router)
    {
        $this->container = $container;
        $this->router = $router;
        $this->listeners = array();
    }

    /**
     * Registers a firewall's LogoutListener, allowing its URL to be generated.
     *
     * @param string                $key           The firewall key
     * @param string                $logoutPath    The path that starts the logout process
     * @param string                $intention     The intention for CSRF token generation
     * @param string                $csrfParameter The CSRF token parameter name
     * @param Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider  A CsrfProviderInterface instance
     */
    public function registerListener($key, $logoutPath, $intention, $csrfParameter, Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface $csrfProvider = null)
    {
        $this->listeners[$key] = array($logoutPath, $intention, $csrfParameter, $csrfProvider);
    }

    /**
     * Generates the absolute logout path for the firewall.
     *
     * @param string $key The firewall key
     *
     * @return string The logout path
     */
    public function getLogoutPath($key)
    {
        return $this->generateLogoutUrl($key, Symfony_Component_Routing_Generator_UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Generates the absolute logout URL for the firewall.
     *
     * @param string $key The firewall key
     *
     * @return string The logout URL
     */
    public function getLogoutUrl($key)
    {
        return $this->generateLogoutUrl($key, Symfony_Component_Routing_Generator_UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Generates the logout URL for the firewall.
     *
     * @param string         $key           The firewall key
     * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The logout URL
     *
     * @throws InvalidArgumentException if no LogoutListener is registered for the key
     */
    private function generateLogoutUrl($key, $referenceType)
    {
        if (!array_key_exists($key, $this->listeners)) {
            throw new InvalidArgumentException(sprintf('No LogoutListener found for firewall key "%s".', $key));
        }

        list($logoutPath, $intention, $csrfParameter, $csrfProvider) = $this->listeners[$key];

        $parameters = null !== $csrfProvider ? array($csrfParameter => $csrfProvider->generateCsrfToken($intention)) : array();

        if ('/' === $logoutPath[0]) {
            $request = $this->container->get('request');

            $url = Symfony_Component_Routing_Generator_UrlGeneratorInterface::ABSOLUTE_URL === $referenceType ? $request->getUriForPath($logoutPath) : $request->getBasePath() . $logoutPath;

            if (!empty($parameters)) {
                $url .= '?' . http_build_query($parameters);
            }
        } else {
            $url = $this->router->generate($logoutPath, $parameters, $referenceType);
        }

        return $url;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'logout_url';
    }
}
