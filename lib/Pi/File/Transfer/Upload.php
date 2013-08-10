<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\File\Transfer;

use Pi;
use Pi\Filter\File\Rename;

/**
 * File upload
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Upload extends Transfer
{
    /**
     * Destination path for uploaded files
     * @var string
     */
    protected $destination;

    /**
     * Creates a file upload handler
     *
     * @param  array   $options   OPTIONAL Options to set for this adapter
     * @param  string  $adapter   Adapter to use
     */
    public function __construct($options = array(), $adapter = 'Http')
    {
        $direction = false;
        $rename = !empty($options['rename']) ? $options['rename'] : '';
        $destination = !empty($options['destination'])
            ? $options['destination'] : Pi::service('module')->current();
        $this->setAdapter($adapter, $direction, $options);
        $this->setDestination($destination);
        $this->setRename($rename);
    }

    /**
     * Returns adapter
     *
     * {@inheritDoc}
     * @note \Zend\File\Transfer\Transfer does not support
     *      for $direction = 1 yet
     */
    public function getAdapter($direction = false)
    {
        $direction = false;

        return parent::getAdapter($direction);
    }

    /**
     * Set upload destination
     *
     * @param string    $value      Absolute path to store files,
     *      or path relative to Pi::path('upload')
     * @param bool      $verify     To very destination path availability
     * @return $this
     */
    public function setDestination($value, $verify = true)
    {
        if (!Pi::service('file')->isAbsolutePath($value)) {
            $path = Pi::path('upload') . '/' . $value;
            if (!is_dir($path)) {
                Pi::service('file')->mkdir($path);
            }
        } else {
            $path = $value;
        }
        if ($verify && !is_dir($path)) {
            Pi::service('file')->mkdir($path);
        }
        $this->destination = $value;
        $this->getAdapter()->setDestination($path);

        return $this;
    }

    /**
     * Get upload destination path
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set upload rename filter
     *
     * @param string|bool $value New name or renaming strategy
     * @return $this
     * @see Pi\Filter\File\Rename for supported renaming strategy
     */
    public function setRename($value = '')
    {
        $value = $value ?: '%random%';
        if (false === $value) {
            $this->getAdapter()->removeFilter('rename');
        } else {
            $this->getAdapter()->removeFilter('rename');
            $this->getAdapter()->addFilter(new Rename($value));
        }

        return $this;
    }

    /**
     * Set upload extensions
     *
     * @param string|array $value
     * @return $this
     */
    public function setExtension($value)
    {
        $this->getAdapter()->addValidator('extension', false, $value);

        return $this;
    }

    /**
     * Set upload excluding extensions
     *
     * @param string|array $value
     * @return $this
     */
    public function setExcludeExtension($value)
    {
        $this->getAdapter()->addValidator('excludeextension', false, $value);

        return $this;
    }

    /**
     * Set file size
     *
     *  If $value is a integer, it will be used as maximum file size
     *
     *  As Array is accepts the following keys:
     *
     *      - 'min': Minimum file size
     *      - 'max': Maximum file size
     *      - 'useByteString': Use bytestring or real size for messages
     *
     * @param int|array $value
     * @return $this
     */
    public function setSize($value)
    {
        $this->getAdapter()->addValidator('size', false, $value);

        return $this;
    }

    /**
     * Set image size
     *
     *  Accepts the following attributes keys:
     *
     *  - minheight
     *  - minwidth
     *  - maxheight
     *  - maxwidth
     *
     * @param array $value
     * @return $this
     */
    public function setImageSize($value)
    {
        $this->getAdapter()->addValidator('imagesize', false, $value);

        return $this;
    }

    /**
     * Get uploaded file(s)
     *
     * @param string    $name  Variable name in upload form
     * @param bool      $path  To include full path
     * @return array
     */
    public function getUploaded($name = null, $path = false)
    {
        $files = $this->getAdapter()->getFileList();
        if ($name) {
            $result = $this->getFileName($name, $path);
            if (isset($files[$name]['multifiles'])) {
                $result = array_values($result);
            }
        } else {
            $multiples = array();
            $result = array();
            foreach ($files as $key => $data) {
                $result[$key] = 1;
                if (isset($data['multifiles'])) {
                    $multiples = array_merge($multiples, $data['multifiles']);
                }
            }
            foreach ($multiples as $key) {
                if (isset($result[$key])) {
                    unset($result[$key]);
                }
            }
            foreach ($result as $key => &$value) {
                $value = $this->getUploaded($key, $path);
            }
        }

        return $result;
    }
}
