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
 * The default session strategy implementation.
 *
 * Supports the following strategies:
 * NONE: the session is not changed
 * MIGRATE: the session id is updated, attributes are kept
 * INVALIDATE: the session id is updated, attributes are lost
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_Security_Http_Session_SessionAuthenticationStrategy implements Symfony_Component_Security_Http_Session_SessionAuthenticationStrategyInterface
{
    const NONE         = 'none';
    const MIGRATE      = 'migrate';
    const INVALIDATE   = 'invalidate';

    private $strategy;

    public function __construct($strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthentication(Symfony_Component_HttpFoundation_Request $request, Symfony_Component_Security_Core_Authentication_Token_TokenInterface $token)
    {
        switch ($this->strategy) {
            case self::NONE:
                return;

            case self::MIGRATE:
                $request->getSession()->migrate();

                return;

            case self::INVALIDATE:
                $request->getSession()->invalidate();

                return;

            default:
                throw new RuntimeException(sprintf('Invalid session authentication strategy "%s"', $this->strategy));
        }
    }
}
