<?php
/**
 * File download
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
 * @package         Pi\File
 */

namespace Pi\File\Transfer;

use Pi;
use Pi\Filter\File\Rename;

/**
 * Download content and files
 *
 * Download conent generated on-fly
 * <code>
 *  $file = array(
 *      'source'        => 'Generated content',
 *      // Required
 *      'type'          => 'string',
 *      // Optional
 *      'filename'      => 'pi-download',
 *      // Optional
 *      'content_type   => 'application/octet-stream',
 *  );
 *  $downloader = new Download;
 *  $downloader->send($file)
 * </code>
 *
 * Download a file
 * <code>
 *  $file = 'path/to/file';
 *  // Or
 *  $file = array(
 *      'source'        => 'path/to/file'
 *      // Optional
 *      'filename'      => 'pi-download',
 *      // Optional
 *      'content_type   => 'application/octet-stream',
 *  );
 *  $downloader = new Download;
 *  $downloader->send($file)
 * </code>
 *
 * Download multiple files, compressed and sent as a zip file
 * <code>
 *  $file = array(
 *      'path/to/file1',
 *      'path/to/file2',
 *      'path/to/file3',
 *  );
 *  // Or
 *  $file = array(
 *      array(
 *          'filename'  => 'path/to/file1',
 *          'localname' => 'filea',
 *      ),
 *      array(
 *          'filename'  => 'path/to/file2',
 *          'localname' => 'fileb',
 *      ),
 *      array(
 *          'filename'  => 'path/to/file3',
 *          'localname' => 'fileb',
 *      ),
 *  );
 *  // Or
 *  $file = array(
 *      'source'        => array(
 *          'path/to/file1',
 *          'path/to/file2',
 *          'path/to/file3',
 *      ),
 *      // Optional
 *      'filename'      => 'pi-download',
 *      // Optional
 *      'type'  => 'zip',
 *      // Optional
 *      'content_type   => 'application/octet-stream',
 *  );
 *  // Or
 *  $file = array(
 *      'source'        => array(
 *          array(
 *              'filename'  => 'path/to/file1',
 *              'localname' => 'filea',
 *          ),
 *          array(
 *              'filename'  => 'path/to/file2',
 *              'localname' => 'fileb',
 *          ),
 *          array(
 *              'filename'  => 'path/to/file3',
 *              'localname' => 'fileb',
 *          ),
 *      ),
 *      // Optional
 *      'filename'      => 'pi-download',
 *      // Optional
 *      'type'  => 'zip',
 *      // Optional
 *      'content_type   => 'application/octet-stream',
 *  );
 *  $downloader = new Download;
 *  $downloader->send($file)
 * </code>
 */
class Download extends Transfer
{
    /**#@+
     * Following methods are not used yet
     */
    /**
     * Filename to be downloaded as
     *
     * @var string
     */
    protected $filename;

    /**
     * Source of the file to be downloaded
     *
     * @var string
     */
    protected $source;

    /**
     * Creates a file download handler
     *
     * @param  array   $options   OPTIONAL Options to set for this adapter
     * @param  string  $adapter   Adapter to use
     */
    public function __construct($options = array(), $adapter = 'Http')
    {
        $direction = false;
        $filename = !empty($options['filename']) ? $options['filename'] : '';
        $source = !empty($options['source']) ? $options['source'] : '';
        $this->setAdapter($adapter, $direction, $options);
        $this->setFilename($filename);
        $this->setSource($source);
    }

    /**
     * Returns adapter
     *
     * {@inheriteDoc}
     * @note Zend\File\Transfer\Transfer does not support for $direction = 1 yet
     */
    public function getAdapter($direction = false)
    {
        $direction = false;
        return parent::getAdapter($direction);
    }

    /**
     * Set download filename
     *
     * @param string $filename
     * @return Download
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get download filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set download source path
     *
     * @param string $source
     * @return Download
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get download source path
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
    /**#@-*/
}
