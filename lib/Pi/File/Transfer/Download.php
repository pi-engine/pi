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
use ZipArchive;

/**
 * Download content and files
 *
 * Download conent generated on-fly
 *
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
 *
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
 *
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
 *
 * Download with specified exit
 *
 * <code>
 *  $downloader = new Download(array('exit' => false));
 *  $downloader->send(array(...));
 *  // Do something
 *  exit;
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Download
{
    /**
     * Exit current execution after the download
     * @var bool
     */
    protected $exit = true;

    /**
     * Path to temporary file for zip file
     * @var string
     */
    protected $tmp = '';

    /**
     * Creates a file download handler
     *
     * @param  array   $options   OPTIONAL Options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param array $options
     * @return $this
     */
    public function setOptions($options = array())
    {
        if (isset($options['exit'])) {
            $this->exit = (bool) $options['exit'];
        }
        $this->tmp = isset($options['tmp'])
            ? $options['tmp'] : Pi::path('cache');

        return $this;
    }

    /**
     * Send the file to the client (Download)
     *
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
        list($resource, $filename, $type, $contentType, $contentLength) =
            $options;
        if ('string' == $type) {
            $source = $resource;
        } else {
            $source = fopen($resource, 'rb');
        }

        // Send the content to client
        $this->download($source, $filename, $contentType, $contentLength);

        // Close resource handler
        if (is_resource($source)) {
            fclose($source);
        }

        // Remove tmp zip file
        if ('zip' == $type) {
            @unlink($resource);
        }

        if ($this->exit) {
            // Exit request to avoid extra output
            exit;
        }

        return true;
    }

    /**
     * Canonize download options
     *
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
            $type           = isset($options['type'])
                ? $options['type'] : 'file';
            $filename       = isset($options['filename'])
                ? $options['filename'] : '';
            $contentType    = isset($options['content_type'])
                ? $options['content_type'] : '';
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
            $resource = array_shift($source);
            $filename = $filename ?: basename($resource);
            $contentLength = filesize($resource);
        } else {
            if ($filename) {
                if (strtolower(substr($filename, -4)) != '.zip') {
                    $filename .= '.zip';
                }
            } else {
                $filename = 'archive.zip';
            }
            $contentType = 'application/zip';
            $zipFile = tempnam($this->tmp, 'zip');
            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
                return array();
            }

            foreach ($source as $item) {
                $localname  = null;
                $file       = null;
                if (is_array($item)) {
                    $file       = $item['filename'];
                    $localname  = isset($item['localname'])
                        ? $item['localname'] : basename($file);
                } elseif (is_file($item)) {
                    $file       = $item;
                    $localname = basename($file);
                } else {
                    continue;
                }
                $zip->addFile($file, $localname);
            }
            $zip->close();
            $resource = $zipFile;
            $contentLength = filesize($resource);
        }

        return array($resource, $filename, $type,
                     $contentType, $contentLength);
    }

    /**
     * Send content to client
     *
     * @param Resource|string $source
     * @param string $filename
     * @param string $contentType
     * @param int $contentLength
     * @return bool
     */
    protected function download(
        $source,
        $filename,
        $contentType,
        $contentLength = 0
    ) {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="'
               . $filename . '"');
        header('Content-Transfer-Encoding: chunked');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        if ($contentLength) {
            header('Content-Length: ' . $contentLength);
        }

        ob_clean();
        flush();
        if (is_resource($source)) {
            //Send the content in chunks
            while (false !== ($chunk = fread($source, 4096))) {
                echo $chunk;
            }
        } elseif (is_string($source)) {
            echo $source;
        }

        return true;
    }

}
