<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Form_Tests_Extension_Core_Type_LocalizedTestCase extends Symfony_Component_Form_Tests_Extension_Core_Type_TypeTestCase
{
    private $backupDefaultLocale;

    protected function setUp()
    {
        parent::setUp();

        if (!class_exists('Symfony_Component_Locale_Locale')) {
            $this->markTestSkipped('The "Locale" component is not available');
        }

        if (!extension_loaded('intl')) {
            $this->markTestSkipped('The "intl" extension is not available');
        }

        $this->backupDefaultLocale = Locale::getDefault();
    }

    protected function tearDown()
    {
        Locale::setDefault($this->backupDefaultLocale);
    }
}
