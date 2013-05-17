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
 * ChainExtractor extracts translation messages from template files.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class Symfony_Component_Translation_Extractor_ChainExtractor implements Symfony_Component_Translation_Extractor_ExtractorInterface
{
    /**
     * The extractors.
     *
     * @var Symfony_Component_Translation_Extractor_ExtractorInterface[]
     */
    private $extractors = array();

    /**
     * Adds a loader to the translation extractor.
     *
     * @param string             $format    The format of the loader
     * @param Symfony_Component_Translation_Extractor_ExtractorInterface $extractor The loader
     */
    public function addExtractor($format, Symfony_Component_Translation_Extractor_ExtractorInterface $extractor)
    {
        $this->extractors[$format] = $extractor;
    }

    /**
     * {@inheritDoc}
     */
    public function setPrefix($prefix)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->setPrefix($prefix);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function extract($directory, Symfony_Component_Translation_MessageCatalogue $catalogue)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->extract($directory, $catalogue);
        }
    }
}
