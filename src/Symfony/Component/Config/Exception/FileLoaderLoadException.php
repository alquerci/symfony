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
 * Exception class for when a resource cannot be loaded or imported.
 *
 * @author Ryan Weaver <ryan@thatsquality.com>
 */
class Symfony_Component_Config_Exception_FileLoaderLoadException extends Exception
{
    /**
     * @var Exception
     */
    private $previous;

    /**
     * @param string     $resource       The resource that could not be imported
     * @param string     $sourceResource The original resource importing the new resource
     * @param integer    $code           The error code
     * @param Exception $previous       A previous exception
     */
    public function __construct($resource, $sourceResource = null, $code = null, $previous = null)
    {
        if (null === $sourceResource) {
            $message = sprintf('Cannot load resource "%s".', $this->varToString($resource));
        } else {
            $message = sprintf('Cannot import resource "%s" from "%s".', $this->varToString($resource), $this->varToString($sourceResource));
        }

        // Is the resource located inside a bundle?
        if ('@' === $resource[0]) {
            $parts = explode(DIRECTORY_SEPARATOR, $resource);
            $bundle = substr($parts[0], 1);
            $message .= ' '.sprintf('Make sure the "%s" bundle is correctly registered and loaded in the application kernel class.', $bundle);
        }

        if (method_exists($this, 'getPrevious')) {
            parent::__construct($message, $code, $previous);
        } else {
            $this->previous = $previous;
            parent::__construct($message, $code);
        }
    }

    protected function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('Object(%s)', get_class($var));
        }

        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, $this->varToString($v));
            }

            return sprintf("Array(%s)", implode(', ', $a));
        }

        if (is_resource($var)) {
            return sprintf('Resource(%s)', get_resource_type($var));
        }

        if (null === $var) {
            return 'null';
        }

        if (false === $var) {
            return 'false';
        }

        if (true === $var) {
            return 'true';
        }

        return (string) $var;
    }

    public function __call($name, array $arguments)
    {
        switch ($name) {
            case 'getPrevious':
                return $this->previous;
            default:
        }

        throw new BadMethodCallException(sprintf('Call an undefined method %s->%s(%s)',
            get_class($this),
            $name,
            implode(', ', array_map('gettype', $arguments))
        ));
    }
}
