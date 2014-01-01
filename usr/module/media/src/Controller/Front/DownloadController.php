<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use ZipArchive;
use Pi;

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
        $id = $this->params('id');
        $ids = explode(',', $id);

        if (empty($ids)) {
            throw new \Exception('Invalid media ID');
        }
        
        // Export files
        $model = $this->getModel('doc');
        $rowset     = $model->select(array('id' => $ids));
        $affectRows = array();
        $files      = array();
        foreach ($rowset as $row) {
            $path = $row['path'];
            if (!$path || !file_exists($path)) {
                continue;
            }
            $files[]      = $path;
            $affectRows[] = $row->id;
        }
        $model->increment('count', array('id' => $ids));
        /*
        if (empty($affectRows)) {
            throw new \Exception('No valid file.');
        }
        
        // Statistics
        $model  = $this->getModel('stats');
        $rowset = $model->select(array('media' => $affectRows));
        $exists = array();
        foreach ($rowset as $row) {
            $exists[] = $row->media;
        }
        
        if (!empty($exists)) {
            foreach ($exists as $item) {
                $row = $model->find($item, 'media');
                $row->fetch_count = $row->fetch_count + 1;
                $row->save();
            }
        }
        
        $newRows = array_diff($affectRows, $exists);
        foreach ($newRows as $item) {
            $data = array(
                'media'       => $item,
                'fetch_count' => 1,
            );
            $row = $model->createRow($data);
            $row->save();
        }
        */
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
            foreach( $files as $file) {
                if (file_exists($file)) {  
                    $zip->addFile( $file , basename($file));
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
    }
    
    /**
     * Output file
     * 
     * @param array $options
     */
    protected function httpOutputFile(array $options)
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
