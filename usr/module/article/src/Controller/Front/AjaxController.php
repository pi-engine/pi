<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Article\Media;

/**
 * Ajax controller
 * 
 * Feature list:
 * 
 * 1. Fuzzy search user by name
 * 2. Fuzzy search tag
 * 3. Check whether an article is exists
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AjaxController extends ActionController
{
    const AJAX_RESULT_TRUE = 1;
    const AJAX_RESULT_FALSE = 0;

    /**
     * Get user name
     * 
     * @return array
     */
    public function getFuzzyUserAction()
    {
        Pi::service('log')->mute();
        $resultset = $result = array();

        $name  = $this->params('name', '');
        $limit = $this->params('limit', 10);

        $where = array();
        if ($name) {
            $where = Pi::db()->where();
            $where->like('identity', "{$name}%");
        }
        $uids  = Pi::user()->getUids($where, $limit, 0, 'identity ASC');
        $result = Pi::user()->get($uids, array('id', 'identity'));

        foreach ($result as $val) {
            $resultset[] = array(
                'id'   => $val['id'],
                'name' => $val['identity'],
            );
        }

        return array(
            'status'    => self::AJAX_RESULT_TRUE,
            'message'   => 'ok',
            'data'      => $resultset,
        );
    }
    
    /**
     * Get author name by AJAX
     *  
     */
    public function getFuzzyAuthorAction()
    {
        Pi::service('log')->mute();
        $resultset = $result = array();

        $name   = $this->params('name', '');
        $limit  = $this->params('limit', 10);

        $model  = $this->getModel('author');
        $select = $model->select()
                ->columns(array('id', 'name', 'photo'))
                ->order('name ASC')
                ->limit($limit);
        if ($name) {
            if (false !== strpos($name, '[')) {
                $name = substr($name, 0, strpos($name, '['));
            }
            $select->where->like('name', "%{$name}%");
            $result = $model->selectWith($select)->toArray();
        }

        foreach ($result as $val) {
            $resultset[] = array(
                'id'    => $val['id'],
                'name'  => $val['name'] . '[' . $val['id'] . ']',
                'photo' => $val['photo'],
            );
        }

        echo json_encode(array(
            'status'    => true,
            'message'   => __('OK'),
            'data'      => $resultset,
        ));
        exit;
    }
    
    /**
     * Save image into indicated folder
     * 
     * @param `name` Folder name under upload folder
     * @param `id`   Session ID or media ID
     * @return JSON
     */
    protected function saveImageAction()
    {
        Pi::service('log')->mute();
        
        $return = array('status' => false);
        
        $uid  = Pi::user()->getId();
        if (empty($uid)) {
            $return['message'] = __('Access denied.');
            echo json_encode($return);
            exit;
        }
        
        $name = $this->params('name', '');
        $id   = $this->params('id', 0);
        
        if (empty($name) || empty($id)) {
            $return['message'] = __('Name or ID is missing');
            echo json_encode($return);
            exit;
        }
        
        // Fetch media detail from media if id is digit, or else try to fetch 
        // it from session.
        $module      = $this->getModule();
        $destination = Media::getTargetDir($name, $module, true, true);
        if (!is_numeric($id)) {
            $session = Media::getUploadSession($module, 'media');
            if (isset($session->$id)) {
                $uploadInfo = $session->$id;

                if ($uploadInfo) {
                    $filename = isset($uploadInfo['tmp_name'])
                        ? Pi::path($uploadInfo['tmp_name']) : '';
                }
                unset($session->$id);
            }
        } else {
            $row = $this->getModel('media')->find($id);
            if ($row->id) {
                $filename = Pi::path($row->url);
            }
        }
        if (!file_exists($filename)) {
            $return['message'] = __('Image is missing.');
            echo json_encode($return);
            exit;
        }
        
        // Copy media to target path
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $basename = sprintf('%s.%s', md5(uniqid($id)), $ext);
        $newPath  = $destination . '/' . $basename;
        Pi::service('file')->copy($filename, Pi::path($newPath));
        if (!is_numeric($id)) {
            @unlink($filename);
        }
        
        // Resize image
        $width  = $this->params('width', 0);
        $height = $this->params('height', 0);
        if (!empty($width) && !empty($height)) {
            Pi::service('image')->resize(Pi::path($newPath), array(
                $width,
                $height
            ));
        }
        
        $id       = $newPath;
        $url      = Pi::url($newPath);
        $title    = substr($basename, 0, strrpos($basename, '.'));
        
        $return = array(
            'status' => true,
            'data'   => array(
                'id'       => $id,
                'url'      => $url,
                'title'    => $title,
                'download' => '',
            )
        );
        echo json_encode($return);
        exit;
    }
}
