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
 * DumperInterface is the interface implemented by all translation dumpers.
 * There is no common option.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
interface Symfony_Component_Translation_Dumper_DumperInterface
{
    /**
     * Dumps the message catalogue.
     *
     * @param Symfony_Component_Translation_MessageCatalogue $messages The message catalogue
     * @param array            $options  Options that are used by the dumper
     */
    public function dump(Symfony_Component_Translation_MessageCatalogue $messages, $options = array());
}
