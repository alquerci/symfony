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
 * This exception is thrown when a circular reference is detected.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Symfony_Component_DependencyInjection_Exception_ServiceCircularReferenceException extends Symfony_Component_DependencyInjection_Exception_RuntimeException
{
    private $serviceId;
    private $path;

    public function __construct($serviceId, array $path)
    {
        parent::__construct(sprintf('Circular reference detected for service "%s", path: "%s".', $serviceId, implode(' -> ', $path)));

        $this->serviceId = $serviceId;
        $this->path = $path;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getPath()
    {
        return $this->path;
    }
}
