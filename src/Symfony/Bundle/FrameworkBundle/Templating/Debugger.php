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
 * Binds the Symfony templating loader debugger to the Symfony logger.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Bundle_FrameworkBundle_Templating_Debugger implements Symfony_Component_Templating_DebuggerInterface
{
    protected $logger;

    /**
     * Constructor.
     *
     * @param Psr_Log_LoggerInterface $logger A LoggerInterface instance
     */
    public function __construct(Psr_Log_LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Logs a message.
     *
     * @param string $message A message to log
     */
    public function log($message)
    {
        if (null !== $this->logger) {
            $this->logger->debug($message);
        }
    }
}
