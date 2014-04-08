<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\File\Transfer;

use Pi;
use ZipArchive;

/**
 * Download content and files
 *
 * Download content generated on-fly
 *
 * <code>
 *  $source = 'Generated content';
 *  $options = array(
 *      // Required
 *      'type'          => 'raw',
 *      // Optional
 *      'filename'      => 'pi-download',
 *      // Optional
 *      'content_type   => 'application/octet-stream',
 *  );
 *  $downloader = new Download;
 *  $downloader->send($source, $options);
 * </code>
 *
 * Download a file
 *
 * <code>
 *  $source = 'path/to/file';
 *  $options = array(
 *      // Optional
 *      'filename'      => 'pi-download',
 *      // Optional
 *      'content_type   => 'application/octet-stream',
 *      // Optional
 *      'content_length => 1234,
 *  );
 *  $downloader = new Download;
 *  $downloader->send($source, options);
 *
 * </code>
 *
 * Download multiple files, compressed and sent as a zip file
 *
 * <code>
 *  $source = array(
 *      'path/to/file1',
 *      'path/to/file2',
 *      'path/to/file3',
 *  );
 *  // Or
 *  $source = array(
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
 *
 *  $options = array(
 *      // Optional
 *      'filename'      => 'pi-download',
 *  );
 *  $downloader = new Download;
 *  $downloader->send($source, $options);
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
     * @param string|array $source File or file meta to download
     * @param array $options Options for the file(s) to send
     *
     * @return bool|void
     */
    public function send($source, array $options = array())
    {
        // Disable logging service
        Pi::service('log')->mute();

        // Canonize download options
        $source = $this->canonizeDownload($source, $options);
        if (!$source) {
            return false;
        }

        if ('raw' == $options['type']) {
            $source = $options['source'];
        } else {
            $source = fopen($source, 'rb');
        }

        // Send the content to client
        $this->download(
            $source,
            $options['filename'],
            $options['content_type'],
            $options['content_length']
        );

        // Close resource handler
        if (is_resource($source)) {
            fclose($source);
        }

        // Remove tmp zip file
        if ('zip' == $options['type']) {
            @unlink($options['source']);
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
     * @param string|array $source File or file meta to download
     * @param array $options Options for the file(s) to send
     *
     * @return string
     */
    protected function canonizeDownload($source, array &$options = array())
    {
        if (!isset($options['type'])) {
            $options['type'] = 'file';
        }
        if (is_array($source)) {
            array_walk($source, function (&$item) {
                if (!is_array($item)) {
                    $item = array('filename' => $item);
                    if (empty($item['localname'])) {
                        $item['localname'] = basename($item['filename']);
                    }
                }
            });
            $zipFile = tempnam($this->tmp, 'zip');
            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
                return array();
            }

            foreach ($source as $item) {
                $zip->addFile($item['filename'], $item['localname']);
            }
            $zip->close();
            $source = $zipFile;
            $options['source'] = $zipFile;

            $options['type'] = 'zip';
            if (!empty($options['filename'])) {
                if (strtolower(substr($options['filename'], -4)) != '.zip') {
                    $options['filename'] .= '.zip';
                }
            } else {
                $options['filename'] = 'archive.zip';
            }
            $options['content_type'] = 'application/zip';
            $this->canonizeDownload($source, $options);

        } elseif ('raw' == $options['type']) {
            if (!isset($options['content_length'])) {
                $options['content_length'] = strlen($source);
            }
        } else {
            if (!isset($options['filename'])) {
                $options['filename'] = basename($source);
            }
            if (!isset($options['content_length'])) {
                $options['content_length'] = filesize($source);
            }
        }
        if (!isset($options['filename'])) {
            $options['filename'] = 'pi-download';
        }
        $options['filename'] = rawurlencode($options['filename']);
        if (!isset($options['content_type'])) {
            $options['content_type'] = 'application/octet-stream';
        }

        return $source;
    }

    /**
     * Send content to client
     *
     * @param Resource|string $source
     * @param string $filename
     * @param string $contentType
     * @param int $contentLength
     *
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
        if (is_resource($source)) {
            // Send the content in chunks
            $buffer     = 1024;
            $readLength = 0;
            while (false !== ($chunk = fread($source, $buffer))
                && $readLength < $contentLength
            ) {
                $readLength += $buffer;
                echo $chunk;
            }
        } elseif (is_string($source)) {
            echo $source;
        }

        return true;
    }

}
