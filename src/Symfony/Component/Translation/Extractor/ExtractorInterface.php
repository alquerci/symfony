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
 * Extracts translation messages from a template directory to the catalogue.
 * New found messages are injected to the catalogue using the prefix.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
interface Symfony_Component_Translation_Extractor_ExtractorInterface
{
    /**
     * Extracts translation messages from a template directory to the catalogue.
     *
     * @param string           $directory The path to look into
     * @param Symfony_Component_Translation_MessageCatalogue $catalogue The catalogue
     */
    public function extract($directory, Symfony_Component_Translation_MessageCatalogue $catalogue);

    /**
     * Sets the prefix that should be used for new found messages.
     *
     * @param string $prefix The prefix
     */
    public function setPrefix($prefix);
}
