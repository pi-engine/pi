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
use Module\Media\Service;
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
        $rowset     = $this->getModel('doc')->select(array('id' => $ids));
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
                $model->increment('count', 1, array('id' => $affectRows));
                /*
                $row = $model->find($item, 'media');
                $row->fetch_count = $row->fetch_count + 1;
                $row->save();
                */
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
        
        $filePath = 'upload/temp';
        Service::mkdir($filePath);
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
        Service::httpOutputFile($options);
    }
}
