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
 * Guesses the mime type with the binary "file" (only available on *nix)
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_HttpFoundation_File_MimeType_FileBinaryMimeTypeGuesser extends Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface
{
    /**
     * @var unknown_type
     *
     * @access private
     */
    var $cmd;

    /**
     * Constructor.
     *
     * The $cmd pattern must contain a "%s" string that will be replaced
     * with the file name to guess.
     *
     * The command output must start with the mime type of the file.
     *
     * @param string $cmd The command to run to get the mime type of a file
     *
     * @access public
     */
    function Symfony_Component_HttpFoundation_File_MimeType_FileBinaryMimeTypeGuesser($cmd = 'file -b --mime %s 2>/dev/null')
    {
        $this->cmd = $cmd;
    }

    /**
     * Returns whether this guesser is supported on the current OS
     *
     * @return Boolean
     *
     * @access public
     *
     * @static
     */
    function isSupported()
    {
        return !defined('PHP_WINDOWS_VERSION_BUILD') && function_exists('passthru') && function_exists('escapeshellarg');
    }

    /**
     * {@inheritdoc}
     *
     * @access public
     */
    function guess($path)
    {
        if (!is_file($path)) {
            trigger_error($path);
        }

        if (!is_readable($path)) {
            trigger_error($path);
        }

        if (!Symfony_Component_HttpFoundation_File_MimeType_FileBinaryMimeTypeGuesser::isSupported()) {
            return null;
        }

        ob_start();

        // need to use --mime instead of -i. see #6641
        passthru(sprintf($this->cmd, escapeshellarg($path)), $return);
        if ($return > 0) {
            ob_end_clean();

            return null;
        }

        $type = trim(ob_get_clean());

        if (!preg_match('#^([a-z0-9\-]+/[a-z0-9\-\.]+)#i', $type, $match)) {
            // it's not a type, but an error message
            return null;
        }

        return $match[1];
    }
}
