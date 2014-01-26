<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Translation_PhpExtractorTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testExtraction()
    {
        // Arrange
        $extractor = new Symfony_Bundle_FrameworkBundle_Translation_PhpExtractor();
        $extractor->setPrefix('prefix');
        $catalogue = new Symfony_Component_Translation_MessageCatalogue('en');

        // Act
        $extractor->extract(dirname(__FILE__).'/../Fixtures/Resources/views/', $catalogue);

        // Assert
        $this->assertCount(1, $catalogue->all('messages'), '->extract() should find 1 translation');
        $this->assertTrue($catalogue->has('new key'), '->extract() should find at leat "new key" message');
        $this->assertEquals('prefixnew key', $catalogue->get('new key'), '->extract() should apply "prefix" as prefix');
    }
}
