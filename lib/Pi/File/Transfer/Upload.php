<?php
/**
 * File upload
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           1.0
 * @package         Pi\File
 * @version         $Id$
 */

namespace Pi\File\Transfer;

use Pi;
use Zend\File\Transfer\Transfer as TransferHandler;
use Pi\Filter\File\Rename;

class Upload extends TransferHandler
{
    protected $destination;

    /**
     * Creates a file upload handler
     *
     * @param  array   $options   OPTIONAL Options to set for this adapter
     * @param  string  $adapter   Adapter to use
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = array(), $adapter = 'Http')
    {
        $direction = false;
        $rename = !empty($options['rename']) ? $options['rename'] : '';
        $destination = !empty($options['destination']) ? $options['destination'] : Pi::service('module')->current();
        $this->setAdapter($adapter, $direction, $options);
        $this->setDestination($destination);
        $this->setRename($rename);
    }

    /**
     * Returns adapter
     *
     * @param boolean $direction On null, all directions are returned
     *                           On false, download direction is returned
     *                           On true, upload direction is returned
     * @return array|Adapter\AbstractAdapter
     */
    public function getAdapter($direction = false)
    {
        return parent::getAdapter($direction);
    }

    /**
     * Set upload destination
     *
     * @param string $value Absolute path to store files, or path relative to Pi::path('upload')
     * @param bool $verify To very destination path availability
     * @return Upload
     */
    public function setDestination($value, $verify = true)
    {
        //if (false === strpos($value, ':') && $value{0} !== '/') {
        if (!Pi::service('file')->isAbsolutePath($value)) {
            $path = Pi::path('upload') . '/' . $value;
            if (!is_dir($path)) {
                //mkdir($path, 0777, true);
                Pi::service('file')->mkdir($path);
            }
        } else {
            $path = $value;
        }
        if ($verify && !is_dir($path)) {
            Pi::service('file')->mkdir($path);
            //throw new \Exception('The destination does not exist: ' . $value);
        }
        $this->destination = $value;
        $this->getAdapter()->setDestination($path);
        return $this;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set upload rename filter
     *
     * @param string|bool $value New name or renaming strategy in case '%' is found
     * @return Upload
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
     * @return Upload
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
     * @return Upload
     */
    public function setExcludeExtension($value)
    {
        $this->getAdapter()->addValidator('excludeextension', false, $value);
        return $this;
    }

    /**
     * Set file size
     *
     * @param int|array $value
     *  If $value is a integer, it will be used as maximum file size
     *  As Array is accepts the following keys:
     *  'min': Minimum file size
     *  'max': Maximum file size
     *  'useByteString': Use bytestring or real size for messages
     * @return Upload
     */
    public function setSize($value)
    {
        $this->getAdapter()->addValidator('size', false, $value);
        return $this;
    }

    /**
     * Set image size
     *
     * @param array $value
     *  Accepts the following option keys:
     *  - minheight
     *  - minwidth
     *  - maxheight
     *  - maxwidth
     * @return Upload
     */
    public function setImageSize($value)
    {
        $this->getAdapter()->addValidator('imagesize', false, $value);
        return $this;
    }

    /**
     * Get uploaded file(s)
     *
     * @param string $name  Variable name in upload form
     * @param bool $path    To include full path
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
            //$singles = array();
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
