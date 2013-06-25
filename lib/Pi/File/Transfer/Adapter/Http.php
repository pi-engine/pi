<?php
/**
 * File transfer HTTP protocol
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

namespace Pi\File\Transfer\Adapter;

use Pi;
use Zend\File\Transfer\Adapter\Http as ZendHttp;
use Zend\File\Exception;
use ZipArchive;

/**
 * {@inheritDoc}
 */
class Http extends ZendHttp
{
    /**
     * {@inheritDoc}
     */
    public function receive($files = null)
    {
        if (!$this->isValid($files)) {
            return false;
        }

        $check = $this->getFiles($files);
        foreach ($check as $file => $content) {
            if (!$content['received']) {
                $directory   = '';
                $destination = $this->getDestination($file);
                if ($destination !== null) {
                    $directory = $destination . DIRECTORY_SEPARATOR;
                }

                $filename = $directory . $content['name'];
                $rename   = $this->getFilter('Rename');
                if ($rename !== null) {
                    /**#@+
                     * Added by Taiwen Jiang
                     */
                    $rename->setSource($content);
                    /**#@-*/
                    $tmp = $rename->getNewName($content['tmp_name']);
                    if ($tmp != $content['tmp_name']) {
                        $filename = $tmp;
                    }

                    if (dirname($filename) == '.') {
                        $filename = $directory . $filename;
                    }

                    $key = array_search(get_class($rename), $this->files[$file]['filters']);
                    unset($this->files[$file]['filters'][$key]);
                }

                // Should never return false when it's tested by the upload validator
                if (!move_uploaded_file($content['tmp_name'], $filename)) {
                    if ($content['options']['ignoreNoFile']) {
                        $this->files[$file]['received'] = true;
                        $this->files[$file]['filtered'] = true;
                        continue;
                    }

                    $this->files[$file]['received'] = false;
                    return false;
                }

                if ($rename !== null) {
                    $this->files[$file]['destination'] = dirname($filename);
                    $this->files[$file]['name']        = basename($filename);
                }

                $this->files[$file]['tmp_name'] = $filename;
                $this->files[$file]['received'] = true;
            }

            if (!$content['filtered']) {
                if (!$this->filter($file)) {
                    $this->files[$file]['filtered'] = false;
                    return false;
                }

                $this->files[$file]['filtered'] = true;
            }
        }

        return true;
    }

    /**#@+
     * Added by Taiwen Jiang
     */
    public function getFileList()
    {
        return $this->files;
    }
    /**#@-*/

    /**
     * Send the file to the client (Download)
     *
     * @see Pi\File\Transfer\Download
     * @param  string|array $options Options for the file(s) to send
     * @return bool
     */
    public function send($options = null)
    {
        // Disable logging service
        Pi::service('log')->mute();

        // Canonize download options
        $options = $this->canonizeDownload($options);
        if (!$options) {
            return false;
        }
        list($resource, $filename, $contentType, $contentLength) = $options;

        // Send the content to client
        $this->download($resource, $filename, $contentType, $contentLength);

        // Close resource handler
        if (is_resource($resource)) {
            fclose($resource);
        }

        // Exit request to avoid extra output
        exit;
    }

    /**
     * Canonize download options
     *
     * @see Pi\File\Transfer\Download
     * @param array|string $options
     * @return array
     */
    protected function canonizeDownload($options)
    {
        $resource       = null;
        $filename       = '';
        $contentType    = 'application/octet-stream';
        $contentLength  = 0;

        $source         = array();
        if (is_array($options) && isset($options['source'])) {
            $source         = (array) $options['source'];
            $type           = isset($options['type']) ? $options['type'] : 'file';
            $filename       = isset($options['filename']) ? $options['filename'] : '';
            $contentType    = isset($options['content_type']) ? $options['content_type'] : '';
        } else {
            $source = (array) $options;
        }
        if (count($source) > 1) {
            $type = 'zip';
        }

        if ('string' == $type) {
            $resource = array_shift($source);
            $contentLength = strlen($resource);
            $source = array();
        } elseif ('zip' != $type) {
            $file = array_shift($source);
            $resource = fopen($file, 'rb');
            $filename = $filename ?: basename($file);
            $contentLength = filesize($file);
        } else {
            if ($filename) {
                if (strtolower(substr($filename, -4)) != '.zip') {
                    $filename .= '.zip';
                }
            } else {
                $filename = 'archive.zip';
            }
            $contentType = 'application/zip';
            $zipFile = tempnam('tmp', 'zip-' . Pi::config('identifier'));
            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
                return array();
            }

            foreach ($source as $item) {
                $localname  = null;
                $file       = null;
                if (is_array($item)) {
                    $file       = $item['filename'];
                    $localname  = isset($item['localname']) ? $item['localname'] : basename($file);
                } elseif (is_file($item)) {
                    $file       = $item;
                    $localname = basename($file);
                } else {
                    continue;
                }
                $zip->addFile($file, $localname);
            }
            $zip->close();
            $resource = fopen($zipFile, 'rb');
            $contentLength = filesize($zipFile);
        }

        return array($resource, $filename, $contentType, $contentLength);
    }

    /**
     * Send content to client
     *
     * @param Resource|string $resource
     * @param string $filename
     * @param string $contentType
     * @param int $contentLength
     * @return bool
     */
    protected function download($resource, $filename, $contentType, $contentLength = 0)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: chunked');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        if ($contentLength) {
            header('Content-Length: ' . $contentLength);
        }

        ob_clean();
        flush();
        if (is_resource($resource)) {
            //Send the content in chunks
            while (false !== ($chunk = fread($resource, 4096))) {
                echo $chunk;
            }
        } elseif (is_string($resource)) {
            echo $resource;
        }

        return true;
    }
}
