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
 * Extends \SplFileInfo to support relative paths
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Symfony_Component_Finder_SplFileInfo extends SplFileInfo
{
    private $relativePath;
    private $relativePathname;

    /**
     * Constructor
     *
     * @param string $file             The file name
     * @param string $relativePath     The relative path
     * @param string $relativePathname The relative path name
     */
    public function __construct($file, $relativePath, $relativePathname)
    {
        parent::__construct($file);
        $this->relativePath = $relativePath;
        $this->relativePathname = $relativePathname;
    }

    /**
     * Returns the relative path
     *
     * @return string the relative path
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * Returns the relative path name
     *
     * @return string the relative path name
     */
    public function getRelativePathname()
    {
        return $this->relativePathname;
    }

    /**
     * Returns the contents of the file
     *
     * @return string the contents of the file
     *
     * @throws RuntimeException
     */
    public function getContents()
    {
        $level = error_reporting(0);
        $content = file_get_contents($this->getRealpath());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $content;
    }

    public function getBasename($suffix = null)
    {
        return basename($this->getPathname(), $suffix);
    }

    public function getExtension()
    {
        return pathinfo($this->getPathname(), PATHINFO_EXTENSION);
    }

    public function getLinkTarget()
    {
        return readlink($this->getPathname());
    }

    public function getRealPath()
    {
        return realpath($this->getPathname());
    }

    public function getPathname()
    {
        return rtrim(parent::getPathname(), DIRECTORY_SEPARATOR);
    }
}
