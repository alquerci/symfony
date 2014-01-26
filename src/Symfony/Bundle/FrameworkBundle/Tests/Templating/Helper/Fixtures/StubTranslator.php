<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_Helper_Fixtures_StubTranslator implements Symfony_Component_Translation_TranslatorInterface
{
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        return '[trans]'.$id.'[/trans]';
    }

    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        return '[trans]'.$id.'[/trans]';
    }

    public function setLocale($locale)
    {
    }

    public function getLocale()
    {
    }
}
