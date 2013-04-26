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
 * Guesses the mime type using the PECL extension FileInfo
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_HttpFoundation_File_MimeType_FileinfoMimeTypeGuesser implements Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface
{
    /**
     * Returns whether this guesser is supported on the current OS/PHP setup
     *
     * @return Boolean
     */
    public static function isSupported()
    {
        return function_exists('finfo_open');
    }

    /**
     * {@inheritdoc}
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new Symfony_Component_HttpFoundation_File_Exception_AccessDeniedException($path);
        }

        if (!self::isSupported()) {
            return null;
        }

        if (!$finfo = new \finfo(FILEINFO_MIME_TYPE)) {
            return null;
        }

        return $finfo->file($path);
    }
}
