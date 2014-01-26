<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Bundle_FrameworkBundle_Tests_Templating_Loader_TemplateLocatorTest extends Symfony_Bundle_FrameworkBundle_Tests_TestCase
{
    public function testLocateATemplate()
    {
        $template = new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('bundle', 'controller', 'name', 'format', 'engine');

        $fileLocator = $this->getFileLocator();

        $fileLocator
            ->expects($this->once())
            ->method('locate')
            ->with($template->getPath())
            ->will($this->returnValue('/path/to/template'))
        ;

        $locator = new Symfony_Bundle_FrameworkBundle_Templating_Loader_TemplateLocator($fileLocator);

        $this->assertEquals('/path/to/template', $locator->locate($template));
    }

    public function testThrowsExceptionWhenTemplateNotFound()
    {
        $template = new Symfony_Bundle_FrameworkBundle_Templating_TemplateReference('bundle', 'controller', 'name', 'format', 'engine');

        $fileLocator = $this->getFileLocator();

        $errorMessage = 'FileLocator exception message';

        $fileLocator
            ->expects($this->once())
            ->method('locate')
            ->will($this->throwException(new InvalidArgumentException($errorMessage)))
        ;

        $locator = new Symfony_Bundle_FrameworkBundle_Templating_Loader_TemplateLocator($fileLocator);

        try {
            $locator->locate($template);
            $this->fail('->locate() should throw an exception when the file is not found.');
        } catch (InvalidArgumentException $e) {
            $this->assertContains(
                $errorMessage,
                $e->getMessage(),
                'TemplateLocator exception should propagate the FileLocator exception message'
            );
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testThrowsAnExceptionWhenTemplateIsNotATemplateReferenceInterface()
    {
        $locator = new Symfony_Bundle_FrameworkBundle_Templating_Loader_TemplateLocator($this->getFileLocator());
        $locator->locate('template');
    }

    protected function getFileLocator()
    {
        return $this->getMock(
            'Symfony_Component_Config_FileLocator',
            array('locate'),
            array('/path/to/fallback')
        );
    }
}
