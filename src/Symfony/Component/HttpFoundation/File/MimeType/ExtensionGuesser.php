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
 * A singleton mime type to file extension guesser.
 *
 * A default guesser is provided.
 * You can register custom guessers by calling the register()
 * method on the singleton instance.
 *
 * <code>
 * $guesser = ExtensionGuesser::getInstance();
 * $guesser->register(new MyCustomExtensionGuesser());
 * </code>
 *
 * The last registered guesser is preferred over previously registered ones.
 *
 */
class Symfony_Component_HttpFoundation_File_MimeType_ExtensionGuesser extends Symfony_Component_HttpFoundation_File_MimeType_ExtensionGuesserInterface
{
    /**
     * The singleton instance
     *
     * @var ExtensionGuesser
     *
     * @access private
     * @static
     */
    var $instance = null;

    /**
     * All registered ExtensionGuesserInterface instances
     *
     * @var array
     *
     * @access protected
     */
     var $guessers = array();

    /**
     * Returns the singleton instance
     *
     * @return ExtensionGuesser
     *
     * @access public
     * @static
     */
    function getInstance()
    {
        if (null ===$this->instance) {
            $this->instance = new Symfony_Component_HttpFoundation_File_MimeType_ExtensionGuesser();
        }

        return $this->instance;
    }

    /**
     * Registers all natively provided extension guessers
     *
     * @access private
     */
    function Symfony_Component_HttpFoundation_File_MimeType_ExtensionGuesser()
    {
        $this->register(new Symfony_Component_HttpFoundation_File_MimeType_MimeTypeExtensionGuesser());
    }

    /**
     * Registers a new extension guesser
     *
     * When guessing, this guesser is preferred over previously registered ones.
     *
     * @param ExtensionGuesserInterface $guesser
     *
     * @access public
     */
    function register($guesser)
    {
        assert(is_a($guesser, 'Symfony_Component_HttpFoundation_File_MimeType_ExtensionGuesserInterface'));

        array_unshift($this->guessers, $guesser);
    }

    /**
     * Tries to guess the extension
     *
     * The mime type is passed to each registered mime type guesser in reverse order
     * of their registration (last registered is queried first). Once a guesser
     * returns a value that is not NULL, this method terminates and returns the
     * value.
     *
     * @param string $mimeType The mime type
     * @return string          The guessed extension or NULL, if none could be guessed
     *
     * @access public
     */
    function guess($mimeType)
    {
        foreach ($this->guessers as $guesser) {
            $extension = $guesser->guess($mimeType);

            if (null !== $extension) {
                break;
            }
        }

        return $extension;
    }
}
