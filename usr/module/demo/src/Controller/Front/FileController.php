<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Demo\Form\FileForm;

class FileController extends ActionController
{
    /**
     * File list
     */
    public function indexAction()
    {
        $path = $this->getUploadPath();
        $filter = function ($fileinfo) {
            if (!$fileinfo->isFile()) {
                return false;
            }
            $filename = $fileinfo->getFilename();
            if ('index.html' == $filename) {
                return false;
            }
            return $filename;
        };
        $list = Pi::service('file')->getList($path, $filter);
        $files = array();
        foreach ($list as $file) {
            $files[] = array(
                'filename'      => $file,
                'url_download'  => $this->url('', array(
                        'action'    => 'download',
                        'file'      => $file,
                    )),
                'url_delete'  => $this->url('', array(
                        'action'    => 'delete',
                        'file'      => $file,
                    )),
            );
        }

        $form = new FileForm;
        $form->setAttribute('action', $this->url('', array('action' => 'upload')));
        $this->view()->assign(array(
            'files' => $files,
            'form'  => $form,
        ));

        $this->view()->setTemplate('file-list');
    }

    public function uploadAction()
    {
        if (!$this->request->isPost()) {
            $result = array(
                'status'    => 'error',
                'message'   => __('Error action.'),
            );
        } else {
            //$rename         = '%random%';
            $rename         = '';
            $destination    = $this->getUploadPath();
            $extensions     = '';
            $maxImageSize   = array();
            $maxFileSize    = 0;

            $post = $this->request->getPost();
            if ('overwrite' != $post['rename']) {
                $rename = '%random%';
            }
            $config = $this->config();
            if (!empty($config['image_extension'])) {
                $exts = explode(',', $config['image_extension']);
                $exts = array_filter(array_walk($exts, 'trim'));
                $extensions = implode(',', $exts);
            }
            if (!empty($config['file_max_size'])) {
                $maxFileSize = (int) $config['file_max_size']  * 1024;
            }
            if (!empty($config['image_max_width'])) {
                $maxImageSize['width'] = (int) $config['image_max_width'];
            }
            if (!empty($config['image_max_height'])) {
                $maxImageSize['height'] = (int) $config['image_max_height'];
            }
            $options = array();
            $options['rename']  = $rename;
            $options['destination']  = $destination;
            if ($extensions) {
                $options['extension'] = $extensions;
            }
            if ($maxFileSize) {
                $options['size'] = $maxFileSize;
            }
            if ($maxImageSize) {
                $options['image_size'] = $maxImageSize;
            }
            $uploader = Pi::service('file')->upload($options);

            if ($uploader->isValid()) {
                $file = $uploader->getUploaded();
                $result = array(
                    'status'    => 'success',
                    'message'   => sprintf(__('File uploaded: %s'), $file),
                );
            } else {
                $messages = $uploader->getMessages();
                $result = array(
                    'status'    => 'error',
                    'message'   => $messages ? implode('; ', $messages) : __('File not uploaded.'),
                );
            }
        }

        $redirect = $this->url('', array('action' => 'index'));
        $this->jump($redirect, $result['message'], $result['status']);
    }

    /**
     * Download a file
     */
    public function downloadAction()
    {
        $path = $this->getUploadPath();
        $filename = _get('file');
        $file = $path . '/' . $filename;
        Pi::service('file')->download($file);
    }

    /**
     * Delete a file
     */
    public function deleteAction()
    {
        $path = $this->getUploadPath();
        $filename = _get('file');
        $file = $path . '/' . $filename;
        Pi::service('file')->remove($file);

        $this->jump(
            array('action' => 'index'),
            sprintf(__('File deleted: %s'), $filename)
        );
    }

    /**
     * Get relative path for upload
     *
     * @return string
     */
    protected function getUploadPath()
    {
        return 'upload/' . $this->getModule();
    }
}