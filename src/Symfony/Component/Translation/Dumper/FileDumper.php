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
 * FileDumper is an implementation of DumperInterface that dump a message catalogue to file(s).
 * Performs backup of already existing files.
 *
 * Options:
 * - path (mandatory): the directory where the files should be saved
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
abstract class Symfony_Component_Translation_Dumper_FileDumper implements Symfony_Component_Translation_Dumper_DumperInterface
{
    /**
     * {@inheritDoc}
     */
    public function dump(Symfony_Component_Translation_MessageCatalogue $messages, $options = array())
    {
        if (!array_key_exists('path', $options)) {
            throw new InvalidArgumentException('The file dumper need a path options.');
        }

        // save a file for each domain
        foreach ($messages->getDomains() as $domain) {
            $file = $domain.'.'.$messages->getLocale().'.'.$this->getExtension();
            // backup
            $fullpath = $options['path'].'/'.$file;
            if (file_exists($fullpath)) {
                copy($fullpath, $fullpath.'~');
            }
            // save file
            file_put_contents($fullpath, $this->format($messages, $domain));
        }
    }

    /**
     * Transforms a domain of a message catalogue to its string representation.
     *
     * @param Symfony_Component_Translation_MessageCatalogue $messages
     * @param string           $domain
     *
     * @return string representation
     */
    abstract protected function format(Symfony_Component_Translation_MessageCatalogue $messages, $domain);

    /**
     * Gets the file extension of the dumper.
     *
     * @return string file extension
     */
    abstract protected function getExtension();
}
