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
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Symfony_Component_Form_Tests_Extension_HttpFoundation_EventListener_BindRequestListenerTest extends PHPUnit_Framework_TestCase
{
    private $values;

    private $filesPlain;

    private $filesNested;

    /**
     * @var Symfony_Component_HttpFoundation_File_UploadedFile
     */
    private $uploadedFile;

    protected function setUp()
    {
        $path = tempnam(sys_get_temp_dir(), 'sf2');
        touch($path);

        $this->values = array(
            'name' => 'Bernhard',
            'image' => array('filename' => 'foobar.png'),
        );

        $this->filesPlain = array(
            'image' => array(
                'error' => UPLOAD_ERR_OK,
                'name' => 'upload.png',
                'size' => 123,
                'tmp_name' => $path,
                'type' => 'image/png'
            ),
        );

        $this->filesNested = array(
            'error' => array('image' => UPLOAD_ERR_OK),
            'name' => array('image' => 'upload.png'),
            'size' => array('image' => 123),
            'tmp_name' => array('image' => $path),
            'type' => array('image' => 'image/png'),
        );

        $this->uploadedFile = new Symfony_Component_HttpFoundation_File_UploadedFile($path, 'upload.png', 'image/png', 123, UPLOAD_ERR_OK);
    }

    protected function tearDown()
    {
        unlink($this->uploadedFile->getRealPath());
    }

    public function requestMethodProvider()
    {
        return array(
            array('POST'),
            array('PUT'),
            array('DELETE'),
            array('PATCH'),
        );
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testBindRequest($method)
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $values = array('author' => $this->values);
        $files = array('author' => $this->filesNested);
        $request = new Symfony_Component_HttpFoundation_Request(array(), $values, array(), array(), $files, array(
            'REQUEST_METHOD' => $method,
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('author', null, $dispatcher);
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        $this->assertEquals(array(
            'name' => 'Bernhard',
            'image' => $this->uploadedFile,
        ), $event->getData());
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testBindRequestWithEmptyName($method)
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $request = new Symfony_Component_HttpFoundation_Request(array(), $this->values, array(), array(), $this->filesPlain, array(
            'REQUEST_METHOD' => $method,
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('', null, $dispatcher);
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        $this->assertEquals(array(
            'name' => 'Bernhard',
            'image' => $this->uploadedFile,
        ), $event->getData());
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testBindEmptyRequestToCompoundForm($method)
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array(
            'REQUEST_METHOD' => $method,
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('author', null, $dispatcher);
        $config->setCompound(true);
        $config->setDataMapper($this->getMock('Symfony_Component_Form_DataMapperInterface'));
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        // Default to empty array
        $this->assertEquals(array(), $event->getData());
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testBindEmptyRequestToSimpleForm($method)
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array(
            'REQUEST_METHOD' => $method,
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('author', null, $dispatcher);
        $config->setCompound(false);
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        // Default to null
        $this->assertNull($event->getData());
    }

    public function testBindGetRequest()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $values = array('author' => $this->values);
        $request = new Symfony_Component_HttpFoundation_Request($values, array(), array(), array(), array(), array(
            'REQUEST_METHOD' => 'GET',
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('author', null, $dispatcher);
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        $this->assertEquals(array(
            'name' => 'Bernhard',
            'image' => array('filename' => 'foobar.png'),
        ), $event->getData());
    }

    public function testBindGetRequestWithEmptyName()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $request = new Symfony_Component_HttpFoundation_Request($this->values, array(), array(), array(), array(), array(
            'REQUEST_METHOD' => 'GET',
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('', null, $dispatcher);
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        $this->assertEquals(array(
            'name' => 'Bernhard',
            'image' => array('filename' => 'foobar.png'),
        ), $event->getData());
    }

    public function testBindEmptyGetRequestToCompoundForm()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array(
            'REQUEST_METHOD' => 'GET',
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('author', null, $dispatcher);
        $config->setCompound(true);
        $config->setDataMapper($this->getMock('Symfony_Component_Form_DataMapperInterface'));
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        $this->assertEquals(array(), $event->getData());
    }

    public function testBindEmptyGetRequestToSimpleForm()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $request = new Symfony_Component_HttpFoundation_Request(array(), array(), array(), array(), array(), array(
            'REQUEST_METHOD' => 'GET',
        ));

        $dispatcher = $this->getMock('Symfony_Component_EventDispatcher_EventDispatcherInterface');
        $config = new Symfony_Component_Form_FormConfigBuilder('author', null, $dispatcher);
        $config->setCompound(false);
        $form = new Symfony_Component_Form_Form($config);
        $event = new Symfony_Component_Form_FormEvent($form, $request);

        $listener = new Symfony_Component_Form_Extension_HttpFoundation_EventListener_BindRequestListener();
        $listener->preBind($event);

        $this->assertNull($event->getData());
    }
}
