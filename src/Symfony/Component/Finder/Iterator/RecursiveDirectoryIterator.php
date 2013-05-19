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
 * Extends the \RecursiveDirectoryIterator to support relative paths
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class Symfony_Component_Finder_Iterator_RecursiveDirectoryIterator extends FileSystemIterator implements RecursiveIterator
{
    /**
     * @var ReflectionObject
     */
    private $ref;

    /**
     * @var string
     */
    private $subPath;

    public function __construct($path, $flags)
    {
        if ($flags & (FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::CURRENT_AS_SELF)) {
            throw new RuntimeException('This iterator only support returning current as fileinfo.');
        }

        parent::__construct($path, $flags);
    }

    /**
     * Return an instance of SplFileInfo with support for relative paths
     *
     * @return Symfony_Component_Finder_SplFileInfo File information
     */
    public function current()
    {
        return new Symfony_Component_Finder_SplFileInfo(parent::current()->getPathname(), $this->getSubPath(), $this->getSubPathname());
    }

    public function hasChildren($allow_links = false)
    {
        if ($this->isDot()) {
            return false;
        }

        if ($this->isDir()) {
            return true;
        }

        if ($this->isLink()) {
            return $allow_links || ($this->getFlags() & FilesystemIterator::FOLLOW_SYMLINKS) === FilesystemIterator::FOLLOW_SYMLINKS;
        }

        return false;
    }

    /**
     * @return Symfony_Component_Finder_Iterator_RecursiveDirectoryIterator
     */
    public function getChildren()
    {
        if ($this->hasChildren()) {
            if (null === $this->ref) {
                $this->ref = new ReflectionObject($this);
            }

            if ($this->isLink()) {
                $path = $this->getLinkTarget();
            } else {
                $path = $this->getPathname();
            }

            if ($this->isDir()) {
                $subPath = $this->subPath ? $this->subPath.DIRECTORY_SEPARATOR.$this->getBasename() : $this->getBasename();
            } else {
                $subPath = $this->subPath;
            }

            $instance = $this->ref->newInstance($path, $this->getFlags());
            $instance->subPath = $subPath;

            return $instance;
        }
    }

    public function getSubPath()
    {
        return (string) $this->subPath;
    }

    public function getSubPathname()
    {
        if (null !== $this->subPath) {
            return $this->subPath.DIRECTORY_SEPARATOR.$this->getFilename();
        }

        return $this->getFilename();
    }
}
