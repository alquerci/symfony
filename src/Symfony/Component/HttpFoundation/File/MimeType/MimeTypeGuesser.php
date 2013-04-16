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
 * A singleton mime type guesser.
 *
 * By default, all mime type guessers provided by the framework are installed
 * (if available on the current OS/PHP setup). You can register custom
 * guessers by calling the register() method on the singleton instance.
 *
 * <code>
 * $guesser = MimeTypeGuesser::getInstance();
 * $guesser->register(new MyCustomMimeTypeGuesser());
 * </code>
 *
 * The last registered guesser is preferred over previously registered ones.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser extends Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface
{
    /**
     * The singleton instance
     *
     * @var MimeTypeGuesser
     *
     * @access private
     *
     * @static
     */
    var $instance = null;

    /**
     * All registered MimeTypeGuesserInterface instances
     *
     * @var array
     *
     * @access protected
     */
    var $guessers = array();

    /**
     * Returns the singleton instance
     *
     * @return MimeTypeGuesser
     *
     * @access public
     *
     * @static
     */
    function getInstance()
    {
        if (null === $this->instance) {
            $this->instance = new Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser();
        }

        return $this->instance;
    }

    /**
     * Registers all natively provided mime type guessers
     *
     * @access private
     */
    function Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser()
    {
        if (Symfony_Component_HttpFoundation_File_MimeType_FileBinaryMimeTypeGuesser::isSupported()) {
            $this->register(new Symfony_Component_HttpFoundation_File_MimeType_FileBinaryMimeTypeGuesser());
        }

        if (Symfony_Component_HttpFoundation_File_MimeType_FileinfoMimeTypeGuesser::isSupported()) {
            $this->register(new Symfony_Component_HttpFoundation_File_MimeType_FileinfoMimeTypeGuesser());
        }
    }

    /**
     * Registers a new mime type guesser
     *
     * When guessing, this guesser is preferred over previously registered ones.
     *
     * @param MimeTypeGuesserInterface $guesser
     *
     * @access public
     */
    function register($guesser)
    {
        assert(is_a($guesser, 'Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface'));

        array_unshift($this->guessers, $guesser);
    }

    /**
     * Tries to guess the mime type of the given file
     *
     * The file is passed to each registered mime type guesser in reverse order
     * of their registration (last registered is queried first). Once a guesser
     * returns a value that is not NULL, this method terminates and returns the
     * value.
     *
     * @param string $path The path to the file
     *
     * @return string         The mime type or NULL, if none could be guessed
     *
     * @throws \LogicException
     * @throws FileNotFoundException
     * @throws AccessDeniedException
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

        if (!$this->guessers) {
            trigger_error('Unable to guess the mime type as no guessers are available (Did you enable the php_fileinfo extension?)');
        }

        foreach ($this->guessers as $guesser) {
            if (null !== $mimeType = $guesser->guess($path)) {
                return $mimeType;
            }
        }
    }
}
