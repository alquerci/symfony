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
 * Guesses the mime type of a file
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface
{
    /**
     * Guesses the mime type of the file with the given path.
     *
     * @param string $path The path to the file
     *
     * @return string         The mime type or NULL, if none could be guessed
     *
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException  If the file does not exist
     * @throws Symfony_Component_HttpFoundation_File_Exception_AccessDeniedException  If the file could not be read
     */
    public function guess($path);
}
