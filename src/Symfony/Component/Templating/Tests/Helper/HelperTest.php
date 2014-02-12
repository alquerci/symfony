<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Templating_Tests_Helper_HelperTest extends PHPUnit_Framework_TestCase
{
    public function testGetSetCharset()
    {
        $helper = new Symfony_Component_Templating_Tests_Helper_ProjectTemplateHelper();
        $helper->setCharset('ISO-8859-1');
        $this->assertTrue('ISO-8859-1' === $helper->getCharset(), '->setCharset() sets the charset set related to this helper');
    }
}

class Symfony_Component_Templating_Tests_Helper_ProjectTemplateHelper extends Symfony_Component_Templating_Helper_Helper
{
    public function getName()
    {
        return 'foo';
    }
}
