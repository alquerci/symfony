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
class Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser implements Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface
{
    /**
     * The singleton instance
     *
     * @var Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser
     */
    private static $instance = null;

    /**
     * All registered MimeTypeGuesserInterface instances
     *
     * @var array
     */
    protected $guessers = array();

    /**
     * Returns the singleton instance
     *
     * @return Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesser
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers all natively provided mime type guessers
     */
    private function __construct()
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
     * @param Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface $guesser
     */
    public function register(Symfony_Component_HttpFoundation_File_MimeType_MimeTypeGuesserInterface $guesser)
    {
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
     * @throws LogicException
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException
     * @throws Symfony_Component_HttpFoundation_File_Exception_AccessDeniedException
     */
    public function guess($path)
    {
        if (!is_file($path)) {
            throw new Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException($path);
        }

        if (!is_readable($path)) {
            throw new Symfony_Component_HttpFoundation_File_Exception_AccessDeniedException($path);
        }

        if (!$this->guessers) {
            throw new LogicException('Unable to guess the mime type as no guessers are available (Did you enable the php_fileinfo extension?)');
        }

        foreach ($this->guessers as $guesser) {
            if (null !== $mimeType = $guesser->guess($path)) {
                return $mimeType;
            }
        }
    }
}
