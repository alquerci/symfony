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
 * A file in the file system.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class Symfony_Component_HttpFoundation_File_File extends SplFileInfo
{
    /**
     * Constructs a new file from the given path.
     *
     * @param string  $path      The path to the file
     * @param Boolean $checkPath Whether to check the path or not
     *
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException If the given path is not a file
     *
     * @api
     */
    public function __construct($path, $checkPath = true)
    {
        if ($checkPath && !is_file($path)) {
            throw new Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException($path);
        }

        parent::__construct($path);
    }

    /**
     * Returns the extension based on the mime type.
     *
     * If the mime type is unknown, returns null.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     *
     * @api
     */
    public function guessExtension()
    {
        $type = $this->getMimeType();
        $guesser = Symfony_Component_HttpFoundation_File_MimeType_ExtensionGuesser::getInstance();

        return $guesser->guess($type);
    }

    /**
     * Returns the mime type of the file.
     *
     * The mime type is guessed using the functions finfo(), mime_content_type()
     * and the system binary "file" (in this order), depending on which of those
     * is available on the current operating system.
     *
     * @return string|null The guessed mime type (i.e. "application/pdf")
     *
     * @api
     */
    public function getMimeType()
    {
        $guesser = Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser::getInstance();

        return $guesser->guess($this->getPathname());
    }

    /**
     * Returns the extension of the file.
     *
     * SplFileInfo::getExtension() is not available before PHP 5.3.6
     *
     * @return string The extension
     *
     * @api
     */
    public function getExtension()
    {
        return pathinfo($this->getBasename(), PATHINFO_EXTENSION);
    }

    public function getBasename($suffix = null)
    {
        return basename($this->getPathname(), $suffix);
    }

    public function getLinkTarget()
    {
        return readlink($this->getPathname());
    }

    public function getRealPath()
    {
        return realpath($this->getPathname());
    }

    /**
     * Moves the file to a new location.
     *
     * @param string $directory The destination folder
     * @param string $name      The new file name
     *
     * @return File A File object representing the new file
     *
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileException if the target file could not be created
     *
     * @api
     */
    public function move($directory, $name = null)
    {
        $target = $this->getTargetFile($directory, $name);

        if (!@rename($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new Symfony_Component_HttpFoundation_File_Exception_FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }

        $umask = false === umask() ? 0022 : umask();
        @chmod($target, 0666 & ~$umask);

        return $target;
    }

    protected function getTargetFile($directory, $name = null)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true)) {
                throw new Symfony_Component_HttpFoundation_File_Exception_FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new Symfony_Component_HttpFoundation_File_Exception_FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $target = $directory.DIRECTORY_SEPARATOR.(null === $name ? $this->getBasename() : $this->getName($name));

        return new Symfony_Component_HttpFoundation_File_File($target, false);
    }

    /**
     * Returns locale independent base name of the given path.
     *
     * @param string $name The new file name
     *
     * @return string containing
     */
    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }
}
