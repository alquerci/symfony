<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class Symfony_Component_Validator_Tests_Constraints_FileValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $context;
    protected $validator;
    protected $path;
    protected $file;

    protected function setUp()
    {
        if (!class_exists('Symfony_Component_HttpFoundation_File_UploadedFile')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        $this->context = $this->getMock('Symfony_Component_Validator_ExecutionContext', array(), array(), '', false);
        $this->validator = new Symfony_Component_Validator_Constraints_FileValidator();
        $this->validator->initialize($this->context);
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'FileValidatorTest';
        $this->file = fopen($this->path, 'w');
    }

    protected function tearDown()
    {
        fclose($this->file);

        $this->context = null;
        $this->validator = null;
        $this->path = null;
        $this->file = null;
    }

    public function testNullIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new Symfony_Component_Validator_Constraints_File());
    }

    public function testEmptyStringIsValid()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new Symfony_Component_Validator_Constraints_File());
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_UnexpectedTypeException
     */
    public function testExpectsStringCompatibleTypeOrFile()
    {
        $this->validator->validate(new stdClass(), new Symfony_Component_Validator_Constraints_File());
    }

    public function testValidFile()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($this->path, new Symfony_Component_Validator_Constraints_File());
    }

    public function testValidUploadedfile()
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $file = new Symfony_Component_HttpFoundation_File_UploadedFile($this->path, 'originalName');
        $this->validator->validate($file, new Symfony_Component_Validator_Constraints_File());
    }

    public function testTooLargeBytes()
    {
        fwrite($this->file, str_repeat('0', 11));

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'maxSize'           => 10,
            'maxSizeMessage'    => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ limit }}'   => '10',
                '{{ size }}'    => '11',
                '{{ suffix }}'  => 'bytes',
                '{{ file }}'    => $this->path,
            ));

        $this->validator->validate($this->getFile($this->path), $constraint);
    }

    public function testTooLargeKiloBytes()
    {
        fwrite($this->file, str_repeat('0', 1400));

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'maxSize'           => '1k',
            'maxSizeMessage'    => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ limit }}'   => '1',
                '{{ size }}'    => '1.4',
                '{{ suffix }}'  => 'kB',
                '{{ file }}'    => $this->path,
            ));

        $this->validator->validate($this->getFile($this->path), $constraint);
    }

    public function testTooLargeMegaBytes()
    {
        fwrite($this->file, str_repeat('0', 1400000));

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'maxSize'           => '1M',
            'maxSizeMessage'    => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ limit }}'   => '1',
                '{{ size }}'    => '1.4',
                '{{ suffix }}'  => 'MB',
                '{{ file }}'    => $this->path,
            ));

        $this->validator->validate($this->getFile($this->path), $constraint);
    }

    /**
     * @expectedException Symfony_Component_Validator_Exception_ConstraintDefinitionException
     */
    public function testInvalidMaxSize()
    {
        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'maxSize' => '1abc',
        ));

        $this->validator->validate($this->path, $constraint);
    }

    public function testValidMimeType()
    {
        $file = $this
            ->getMockBuilder('Symfony_Component_HttpFoundation_File_File')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->path))
        ;
        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue('image/jpg'))
        ;

        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'mimeTypes' => array('image/png', 'image/jpg'),
        ));

        $this->validator->validate($file, $constraint);
    }

    public function testValidWildcardMimeType()
    {
        $file = $this
            ->getMockBuilder('Symfony_Component_HttpFoundation_File_File')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->path))
        ;
        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue('image/jpg'))
        ;

        $this->context->expects($this->never())
            ->method('addViolation');

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'mimeTypes' => array('image/*'),
        ));

        $this->validator->validate($file, $constraint);
    }

    public function testInvalidMimeType()
    {
        $file = $this
            ->getMockBuilder('Symfony_Component_HttpFoundation_File_File')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->path))
        ;
        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue('application/pdf'))
        ;

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'mimeTypes' => array('image/png', 'image/jpg'),
            'mimeTypesMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ type }}'    => '"application/pdf"',
                '{{ types }}'   => '"image/png", "image/jpg"',
                '{{ file }}'    => $this->path,
            ));

        $this->validator->validate($file, $constraint);
    }

    public function testInvalidWildcardMimeType()
    {
        $file = $this
            ->getMockBuilder('Symfony_Component_HttpFoundation_File_File')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $file
            ->expects($this->once())
            ->method('getPathname')
            ->will($this->returnValue($this->path))
        ;
        $file
            ->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue('application/pdf'))
        ;

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            'mimeTypes' => array('image/*', 'image/jpg'),
            'mimeTypesMessage' => 'myMessage',
        ));

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('myMessage', array(
                '{{ type }}'    => '"application/pdf"',
                '{{ types }}'   => '"image/*", "image/jpg"',
                '{{ file }}'    => $this->path,
            ));

        $this->validator->validate($file, $constraint);
    }

    /**
     * @dataProvider uploadedFileErrorProvider
     */
    public function testUploadedFileError($error, $message, array $params = array(), $maxSize = null)
    {
        $file = new Symfony_Component_HttpFoundation_File_UploadedFile('/path/to/file', 'originalName', 'mime', 0, $error);

        $constraint = new Symfony_Component_Validator_Constraints_File(array(
            $message => 'myMessage',
            'maxSize' => $maxSize
        ));

        $addViolationMocker = $this->context->expects($this->once())
            ->method('addViolation')
        ;

        if (empty($params)) {
            $addViolationMocker->with('myMessage');
        } else {
            $addViolationMocker->with('myMessage', $params);
        }

        $this->validator->validate($file, $constraint);

    }

    public function uploadedFileErrorProvider()
    {
        $tests = array(
            array(UPLOAD_ERR_FORM_SIZE, 'uploadFormSizeErrorMessage'),
            array(UPLOAD_ERR_PARTIAL, 'uploadPartialErrorMessage'),
            array(UPLOAD_ERR_NO_FILE, 'uploadNoFileErrorMessage'),
            array(UPLOAD_ERR_NO_TMP_DIR, 'uploadNoTmpDirErrorMessage'),
            array(UPLOAD_ERR_CANT_WRITE, 'uploadCantWriteErrorMessage'),
            array(UPLOAD_ERR_EXTENSION, 'uploadExtensionErrorMessage'),
        );

        if (class_exists('Symfony_Component_HttpFoundation_File_UploadedFile')) {
            // when no maxSize is specified on constraint, it should use the ini value
            $tests[] = array(UPLOAD_ERR_INI_SIZE, 'uploadIniSizeErrorMessage', array(
                '{{ limit }}' => Symfony_Component_HttpFoundation_File_UploadedFile::getMaxFilesize(),
                '{{ suffix }}' => 'bytes',
            ));

            // it should use the smaller limitation (maxSize option in this case)
            $tests[] = array(UPLOAD_ERR_INI_SIZE, 'uploadIniSizeErrorMessage', array(
                '{{ limit }}' => 1,
                '{{ suffix }}' => 'bytes',
            ), '1');

            // it correctly parses the maxSize option and not only uses simple string comparison
            // 1000M should be bigger than the ini value
            $tests[] = array(UPLOAD_ERR_INI_SIZE, 'uploadIniSizeErrorMessage', array(
                '{{ limit }}' => Symfony_Component_HttpFoundation_File_UploadedFile::getMaxFilesize(),
                '{{ suffix }}' => 'bytes',
            ), '1000M');
        }

        return $tests;
    }

    abstract protected function getFile($filename);
}
