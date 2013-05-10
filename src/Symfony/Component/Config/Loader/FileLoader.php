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
 * FileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Symfony_Component_Config_Loader_FileLoader extends Symfony_Component_Config_Loader_Loader
{
    protected static $loading = array();

    protected $locator;

    private $currentDir;

    /**
     * Constructor.
     *
     * @param Symfony_Component_Config_FileLocatorInterface $locator A FileLocatorInterface instance
     */
    public function __construct(Symfony_Component_Config_FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function setCurrentDir($dir)
    {
        $this->currentDir = $dir;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Imports a resource.
     *
     * @param mixed   $resource       A Resource
     * @param string  $type           The resource type
     * @param Boolean $ignoreErrors   Whether to ignore import errors or not
     * @param string  $sourceResource The original resource importing the new resource
     *
     * @return mixed
     *
     * @throws Symfony_Component_Config_Exception_FileLoaderLoadException
     * @throws Symfony_Component_Config_Exception_FileLoaderImportCircularReferenceException
     */
    public function import($resource, $type = null, $ignoreErrors = false, $sourceResource = null)
    {
        try {
            $loader = $this->resolve($resource, $type);

            if ($loader instanceof Symfony_Component_Config_Loader_FileLoader && null !== $this->currentDir) {
                $resource = $this->locator->locate($resource, $this->currentDir);
            }

            if (isset(self::$loading[$resource])) {
                throw new Symfony_Component_Config_Exception_FileLoaderImportCircularReferenceException(array_keys(self::$loading));
            }
            self::$loading[$resource] = true;

            $ret = $loader->load($resource, $type);

            unset(self::$loading[$resource]);

            return $ret;
        } catch (Symfony_Component_Config_Exception_FileLoaderImportCircularReferenceException $e) {
            throw $e;
        } catch (Exception $e) {
            if (!$ignoreErrors) {
                // prevent embedded imports from nesting multiple exceptions
                if ($e instanceof Symfony_Component_Config_Exception_FileLoaderLoadException) {
                    throw $e;
                }

                throw new Symfony_Component_Config_Exception_FileLoaderLoadException($resource, $sourceResource, null, $e);
            }
        }
    }
}
