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
 * Thrown when a file was not found
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException extends Symfony_Component_HttpFoundation_File_Exception_FileException
{
    /**
     * Constructor.
     *
     * @param string $path The path to the file that was not found
     */
    public function __construct($path)
    {
        parent::__construct(sprintf('The file "%s" does not exist', $path));
    }
}
