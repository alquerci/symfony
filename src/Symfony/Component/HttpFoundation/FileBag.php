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
 * FileBag is a container for HTTP headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * @api
 */
class Symfony_Component_HttpFoundation_FileBag extends Symfony_Component_HttpFoundation_ParameterBag
{
    /**
     * @access private
     * @static
     */
    var $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');

    function Symfony_Component_HttpFoundation_FileBag($parameters = array())
    {
        $this->__construct($parameters);
    }

    /**
     * Constructor.
     *
     * @param array $parameters An array of HTTP files
     *
     * @api
     *
     * @access public
     */
    function __construct($parameters = array())
    {
        assert(is_array($parameters));

        $this->replace($parameters);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @access public
     */
    function replace($files = array())
    {
        assert(is_array($files));

        $this->parameters = array();
        $this->add($files);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @access public
     */
    function set($key, $value)
    {
        if (!is_array($value) && !is_a($value, 'Symfony_Component_HttpFoundation_File_UploadedFile')) {
            trigger_error('An uploaded file must be an array or an instance of UploadedFile.');
        }

        parent::set($key, $this->convertFileInformation($value));
    }

    /**
     * {@inheritdoc}
     *
     * @api
     *
     * @access public
     */
    function add($files = array())
    {
        assert(is_array($files));

        foreach ($files as $key => $file) {
            $this->set($key, $file);
        }
    }

    /**
     * Converts uploaded files to UploadedFile instances.
     *
     * @param array|UploadedFile $file A (multi-dimensional) array of uploaded file information
     *
     * @return array A (multi-dimensional) array of UploadedFile instances
     *
     * @access protected
     */
    function convertFileInformation($file)
    {
        if (is_a($file, 'Symfony_Component_HttpFoundation_File_UploadedFile')) {
            return $file;
        }

        $file = $this->fixPhpFilesArray($file);
        if (is_array($file)) {
            $keys = array_keys($file);
            sort($keys);

            if ($keys == $this->fileKeys) {
                if (UPLOAD_ERR_NO_FILE == $file['error']) {
                    $file = null;
                } else {
                    $file = new Symfony_Component_HttpFoundation_File_UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
                }
            } else {
                $file = array_map(array($this, 'convertFileInformation'), $file);
            }
        }

        return $file;
    }

    /**
     * Fixes a malformed PHP $_FILES array.
     *
     * PHP has a bug that the format of the $_FILES array differs, depending on
     * whether the uploaded file fields had normal field names or array-like
     * field names ("normal" vs. "parent[child]").
     *
     * This method fixes the array to look like the "normal" $_FILES array.
     *
     * It's safe to pass an already converted array, in which case this method
     * just returns the original array unmodified.
     *
     * @param array $data
     *
     * @return array
     *
     * @access protected
     */
    function fixPhpFilesArray($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $keys = array_keys($data);
        sort($keys);

        if ($this->fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
            return $data;
        }

        $files = $data;
        foreach ($this->fileKeys as $k) {
            unset($files[$k]);
        }

        foreach (array_keys($data['name']) as $key) {
            $files[$key] = $this->fixPhpFilesArray(array(
                'error'    => $data['error'][$key],
                'name'     => $data['name'][$key],
                'type'     => $data['type'][$key],
                'tmp_name' => $data['tmp_name'][$key],
                'size'     => $data['size'][$key]
            ));
        }

        return $files;
    }
}
