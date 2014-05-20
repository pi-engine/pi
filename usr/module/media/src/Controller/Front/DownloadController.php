<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Media\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\File\Transfer\Download;

/**
 * Download controller
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DownloadController extends ActionController
{
    /**
     * Download media
     */
    public function indexAction()
    {
        // Disable debugger message
        Pi::service('log')->mute();

        $id = $this->params('id');
        $ids = array_filter(explode(',', $id));

        if (empty($ids)) {
            if (substr(PHP_SAPI, 0, 3) == 'cgi') {
                header('Status: 404 Not Found');
            } else {
                header('HTTP/1.1 404 Not Found');
            }

            exit;
        }

        // Export files
        $model  = $this->getModel('doc');
        $rowset = $model->select(array('id' => $ids));
        $files  = array();
        foreach ($rowset as $row) {
            $path = $row['path'];
            if (!$path || !file_exists($path)) {
                continue;
            }
            $files[] = array(
                'source'            => $path,
                'filename'          => $row['filename'],
                'content_type'      => $row['mimetype'],
                'content_length'    => $row['size'],
            );
        }
        $model->increment('count', array('id' => $ids));
        if (1 == count($files)) {
            $files      = current($files);
            $source     = $files['source'];
            $options    = $files;
        } else {
            $source     = $files;
            $options    = array();
        }

        // This code cannot output file
        $downloader = new Download();
        $result = $downloader->send($source, $options);
        if (false === $result) {
            if (substr(PHP_SAPI, 0, 3) == 'cgi') {
                header('Status: 404 Not Found');
            } else {
                header('HTTP/1.1 404 Not Found');
            }
        }

        exit;

        /*
        $filePath = 'upload/tmp';
        Pi::service('file')->mkdir(Pi::path($filePath));
        $filename = sprintf('%s/media-%s.zip', $filePath, time());
        $filename = Pi::path($filename);
        $zip      = new ZipArchive();
        if ($zip->open($filename, ZIPARCHIVE::CREATE)!== TRUE) {
            exit ;
        }
        $compress = count($files) > 1 ? true : false;
        if ($compress) {
            foreach ($files as $file) {
                if (file_exists($file)) {  
                    $zip->addFile($file, basename($file));
                }
            }  
            $zip->close();
        } else {
            $filename = Pi::path(array_shift($files));
        }
        
        $options = array(
            'file'       => $filename,
            'fileName'   => basename($filename),
        );
        if ($compress) {
            $options['deleteFile'] = true;
        }
        $this->httpOutputFile($options);
        */
    }
    
    /**
     * Output file
     * 
     * @param array $options
     */
    protected function ____httpOutputFile(array $options)
    {
        if ((!isset($options['file']) && !isset($options['raw']))) {
            if (!$options['silent']) {
                header('HTTP/1.0 404 Not Found');
            }
            exit();
        }
        if (isset($options['file']) && !is_file($options['file'])) {
            if (!$options['silent']) {
                header('HTTP/1.0 403 Forbidden');
            }
            exit();
        }
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            $options['fileName'] = urlencode($options['fileName']);
        }
        $options['fileSize'] = isset($options['file']) 
            ? filesize($options['file']) : strlen($options['raw']);

        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        header("Pragma: public");
        header('Content-Description: File Transfer');
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) {
            header('Content-Type: application/force-download; charset=UTF-8');
        } else {
            header('Content-Type: application/octet-stream; charset=UTF-8');
        }
        header('Content-Disposition: attachment; filename="' . $options['fileName'] . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Pragma: public');
        header('Content-Length: ' . $options['fileSize']);
        ob_clean();
        flush();

        if (!empty($options['file'])) {
            readfile($options['file']);
            if (!empty($options['deleteFile'])) {
                @unlink($options['file']);
            }
        } else {
            echo $options['raw'];
            ob_flush();
            flush();
        }
        if (empty($options['notExit'])) {
            exit();
        }
    }
}
