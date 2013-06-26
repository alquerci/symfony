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
 * AnonymousAuthenticationListener automatically adds a Token if none is
 * already present.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Security_Http_Firewall_AnonymousAuthenticationListener implements Symfony_Component_Security_Http_Firewall_ListenerInterface
{
    private $context;
    private $key;
    private $logger;

    public function __construct(Symfony_Component_Security_Core_SecurityContextInterface $context, $key, Psr_Log_LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->key     = $key;
        $this->logger  = $logger;
    }

    /**
     * Handles anonymous authentication.
     *
     * @param Symfony_Component_HttpKernel_Event_GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(Symfony_Component_HttpKernel_Event_GetResponseEvent $event)
    {
        if (null !== $this->context->getToken()) {
            return;
        }

        $this->context->setToken(new Symfony_Component_Security_Core_Authentication_Token_AnonymousToken($this->key, 'anon.', array()));

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Populated SecurityContext with an anonymous Token'));
        }
    }
}
