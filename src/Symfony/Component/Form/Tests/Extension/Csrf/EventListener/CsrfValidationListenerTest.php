<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Symfony_Component_Form_Tests_Extension_Csrf_EventListener_CsrfValidationListenerTest extends PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    protected $factory;
    protected $csrfProvider;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_EventDispatcher_EventDispatcher')) {
            $this->markTestSkipped('The "EventDispatcher" component is not available');
        }

        $this->dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $this->factory = $this->getMock('Symfony_Component_Form_FormFactoryInterface');
        $this->csrfProvider = $this->getMock('Symfony_Component_Form_Extension_Csrf_CsrfProvider_CsrfProviderInterface');
        $this->form = $this->getBuilder('post')
            ->setDataMapper($this->getDataMapper())
            ->getForm();
    }

    protected function tearDown()
    {
        $this->dispatcher = null;
        $this->factory = null;
        $this->csrfProvider = null;
        $this->form = null;
    }

    protected function getBuilder($name = 'name')
    {
        return new Symfony_Component_Form_FormBuilder($name, null, $this->dispatcher, $this->factory, array('compound' => true));
    }

    protected function getForm($name = 'name')
    {
        return $this->getBuilder($name)->getForm();
    }

    protected function getDataMapper()
    {
        return $this->getMock('Symfony_Component_Form_DataMapperInterface');
    }

    protected function getMockForm()
    {
        return $this->getMock('Symfony_Component_Form_Test_FormInterface');
    }

    // https://github.com/symfony/symfony/pull/5838
    public function testStringFormData()
    {
        $data = "XP4HUzmHPi";
        $event = new Symfony_Component_Form_FormEvent($this->form, $data);

        $validation = new Symfony_Component_Form_Extension_Csrf_EventListener_CsrfValidationListener('csrf', $this->csrfProvider, 'unknown');
        $validation->preBind($event);

        // Validate accordingly
        $this->assertSame($data, $event->getData());
    }
}
