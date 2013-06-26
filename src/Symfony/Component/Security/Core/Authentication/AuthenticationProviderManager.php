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
 * AuthenticationProviderManager uses a list of AuthenticationProviderInterface
 * instances to authenticate a Token.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Core_Authentication_AuthenticationProviderManager implements Symfony_Component_Security_Core_Authentication_AuthenticationManagerInterface
{
    private $providers;
    private $eraseCredentials;
    private $eventDispatcher;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Security_Core_Authentication_Provider_AuthenticationProviderInterface[] $providers        An array of AuthenticationProviderInterface instances
     * @param Boolean                           $eraseCredentials Whether to erase credentials after authentication or not
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $providers, $eraseCredentials = true)
    {
        if (!$providers) {
            throw new InvalidArgumentException('You must at least add one authentication provider.');
        }

        $this->providers = $providers;
        $this->eraseCredentials = (Boolean) $eraseCredentials;
    }

    public function setEventDispatcher(Symfony_Component_EventDispatcher_EventDispatcherInterface $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        $lastException = null;
        $result = null;

        foreach ($this->providers as $provider) {
            if (!$provider->supports($token)) {
                continue;
            }

            try {
                $result = $provider->authenticate($token);

                if (null !== $result) {
                    break;
                }
            } catch (Symfony_Component_Security_Core_Exception_AccountStatusException $e) {
                $e->setToken($token);

                throw $e;
            } catch (Symfony_Component_Security_Core_Exception_AuthenticationException $e) {
                $lastException = $e;
            }
        }

        if (null !== $result) {
            if (true === $this->eraseCredentials) {
                $result->eraseCredentials();
            }

            if (null !== $this->eventDispatcher) {
                $this->eventDispatcher->dispatch(Symfony_Component_Security_Core_AuthenticationEvents::AUTHENTICATION_SUCCESS, new Symfony_Component_Security_Core_Event_AuthenticationEvent($result));
            }

            return $result;
        }

        if (null === $lastException) {
            $lastException = new Symfony_Component_Security_Core_Exception_ProviderNotFoundException(sprintf('No Authentication Provider found for token of class "%s".', get_class($token)));
        }

        if (null !== $this->eventDispatcher) {
            $this->eventDispatcher->dispatch(Symfony_Component_Security_Core_AuthenticationEvents::AUTHENTICATION_FAILURE, new Symfony_Component_Security_Core_Event_AuthenticationFailureEvent($token, $lastException));
        }

        $lastException->setToken($token);

        throw $lastException;
    }
}
