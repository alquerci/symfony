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
 * Base exception for all adapter failures.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
class Symfony_Component_Finder_Exception_AdapterFailureException extends RuntimeException implements Symfony_Component_Finder_Exception_ExceptionInterface
{
    /**
     * @var Symfony_Component_Finder_Adapter_AdapterInterface
     */
    private $adapter;

    /**
     * @param Symfony_Component_Finder_Adapter_AdapterInterface $adapter
     * @param string|null      $message
     * @param Exception|null  $previous
     */
    public function __construct(Symfony_Component_Finder_Adapter_AdapterInterface $adapter, $message = null, Exception $previous = null)
    {
        $this->adapter = $adapter;
        parent::__construct($message ? $message : 'Search failed with "'.$adapter->getName().'" adapter.', $previous);
    }

    /**
     * {@inheritdoc}
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
