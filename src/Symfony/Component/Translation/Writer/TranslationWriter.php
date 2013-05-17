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
 * TranslationWriter writes translation messages.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class Symfony_Component_Translation_Writer_TranslationWriter
{
    /**
     * Dumpers used for export.
     *
     * @var array
     */
    private $dumpers = array();

    /**
     * Adds a dumper to the writer.
     *
     * @param string          $format The format of the dumper
     * @param Symfony_Component_Translation_Dumper_DumperInterface $dumper The dumper
     */
    public function addDumper($format, Symfony_Component_Translation_Dumper_DumperInterface $dumper)
    {
        $this->dumpers[$format] = $dumper;
    }

    /**
     * Obtains the list of supported formats.
     *
     * @return array
     */
    public function getFormats()
    {
        return array_keys($this->dumpers);
    }

    /**
     * Writes translation from the catalogue according to the selected format.
     *
     * @param Symfony_Component_Translation_MessageCatalogue $catalogue The message catalogue to dump
     * @param string           $format    The format to use to dump the messages
     * @param array            $options   Options that are passed to the dumper
     *
     * @throws InvalidArgumentException
     */
    public function writeTranslations(Symfony_Component_Translation_MessageCatalogue $catalogue, $format, $options = array())
    {
        if (!isset($this->dumpers[$format])) {
            throw new InvalidArgumentException(sprintf('There is no dumper associated with format "%s".', $format));
        }

        // get the right dumper
        $dumper = $this->dumpers[$format];

        // save
        $dumper->dump($catalogue, $options);
    }
}
