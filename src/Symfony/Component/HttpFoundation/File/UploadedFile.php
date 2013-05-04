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
 * A file uploaded through a form.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class Symfony_Component_HttpFoundation_File_UploadedFile extends Symfony_Component_HttpFoundation_File_File
{
    /**
     * Whether the test mode is activated.
     *
     * Local files are used in test mode hence the code should not enforce HTTP uploads.
     *
     * @var Boolean
     */
    private $test = false;

    /**
     * The original name of the uploaded file.
     *
     * @var string
     */
    private $originalName;

    /**
     * The mime type provided by the uploader.
     *
     * @var string
     */
    private $mimeType;

    /**
     * The file size provided by the uploader.
     *
     * @var string
     */
    private $size;

    /**
     * The UPLOAD_ERR_XXX constant provided by the uploader.
     *
     * @var integer
     */
    private $error;

    /**
     * Accepts the information of the uploaded file as provided by the PHP global $_FILES.
     *
     * The file object is only created when the uploaded file is valid (i.e. when the
     * isValid() method returns true). Otherwise the only methods that could be called
     * on an UploadedFile instance are:
     *
     *   * getClientOriginalName,
     *   * getClientMimeType,
     *   * isValid,
     *   * getError.
     *
     * Calling any other method on an non-valid instance will cause an unpredictable result.
     *
     * @param string  $path         The full temporary path to the file
     * @param string  $originalName The original file name
     * @param string  $mimeType     The type of the file as provided by PHP
     * @param integer $size         The file size
     * @param integer $error        The error constant of the upload (one of PHP's UPLOAD_ERR_XXX constants)
     * @param Boolean $test         Whether the test mode is active
     *
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileException         If file_uploads is disabled
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileNotFoundException If the file does not exist
     *
     * @api
     */
    public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null, $test = false)
    {
        if (!ini_get('file_uploads')) {
            throw new Symfony_Component_HttpFoundation_File_Exception_FileException(sprintf('Unable to create UploadedFile because "file_uploads" is disabled in your php.ini file (%s)', get_cfg_var('cfg_file_path')));
        }

        $this->originalName = $this->getName($originalName);
        $this->mimeType = $mimeType ? $mimeType : 'application/octet-stream';
        $this->size = $size;
        $this->error = $error ? $error : UPLOAD_ERR_OK;
        $this->test = (Boolean) $test;

        parent::__construct($path, UPLOAD_ERR_OK === $this->error);
    }

    /**
     * Returns the original file name.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then is should not be considered as a safe value.
     *
     * @return string|null The original name
     *
     * @api
     */
    public function getClientOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Returns the original file extension
     *
     * It is extracted from the original file name that was uploaded.
     * Then is should not be considered as a safe value.
     *
     * @return string The extension
     */
    public function getClientOriginalExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    /**
     * Returns the file mime type.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then is should not be considered as a safe value.
     *
     * @return string|null The mime type
     *
     * @api
     */
    public function getClientMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Returns the file size.
     *
     * It is extracted from the request from which the file has been uploaded.
     * Then is should not be considered as a safe value.
     *
     * @return integer|null The file size
     *
     * @api
     */
    public function getClientSize()
    {
        return $this->size;
    }

    /**
     * Returns the upload error.
     *
     * If the upload was successful, the constant UPLOAD_ERR_OK is returned.
     * Otherwise one of the other UPLOAD_ERR_XXX constants is returned.
     *
     * @return integer The upload error
     *
     * @api
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns whether the file was uploaded successfully.
     *
     * @return Boolean True if no error occurred during uploading
     *
     * @api
     */
    public function isValid()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * Moves the file to a new location.
     *
     * @param string $directory The destination folder
     * @param string $name      The new file name
     *
     * @return Symfony_Component_HttpFoundation_File_File A File object representing the new file
     *
     * @throws Symfony_Component_HttpFoundation_File_Exception_FileException if the file has not been uploaded via Http
     *
     * @api
     */
    public function move($directory, $name = null)
    {
        if ($this->isValid()) {
            if ($this->test) {
                return parent::move($directory, $name);
            } elseif (is_uploaded_file($this->getPathname())) {
                $target = $this->getTargetFile($directory, $name);

                if (!@move_uploaded_file($this->getPathname(), $target)) {
                    $error = error_get_last();
                    throw new Symfony_Component_HttpFoundation_File_Exception_FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
                }

                $umask = false === umask() ? 0022 : umask();
                @chmod($target, 0666 & ~$umask);

                return $target;
            }
        }

        throw new Symfony_Component_HttpFoundation_File_Exception_FileException(sprintf('The file "%s" has not been uploaded via Http', $this->getPathname()));
    }

    /**
     * Returns the maximum size of an uploaded file as configured in php.ini
     *
     * @return int The maximum size of an uploaded file in bytes
     */
    public static function getMaxFilesize()
    {
        $max = trim(ini_get('upload_max_filesize'));

        if ('' === $max) {
            return PHP_INT_MAX;
        }

        switch (strtolower(substr($max, -1))) {
            case 'g':
                $max *= 1024;
            case 'm':
                $max *= 1024;
            case 'k':
                $max *= 1024;
        }

        return (integer) $max;
    }
}
