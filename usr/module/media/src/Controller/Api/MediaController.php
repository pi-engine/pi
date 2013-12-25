<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Media\Controller\Api;

use Pi\Mvc\Controller\ApiController;
use Pi\File\Transfer\Upload;

/**
 * User webservice controller
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaController extends ApiController
{
    /**
     * {@inheritDoc}
     */
    protected $modelName = 'doc';

    /**
     * Upload a file
     */
    public function uploadAction()
    {
        $uriRoot = $this->getUriRoot();
        $pathRoot = $this->getPathRoot();
        $target = $this->generateName();
        $success = false;
        switch ($this->request->getMethod()) {
            case 'POST':
                $uploader = new Upload(array(
                    'destination'   => $pathRoot,
                    'rename'        => $target,
                ));
                if ($uploader->isValid()) {
                    $uploader->receive();
                    $success = true;
                }
                break;
            case 'PUT':
                $putdata = fopen('php://input', 'r');
                $fp = fopen($pathRoot . '/' . $target, 'w');
                while ($data = fread($putdata, 1024)) {
                    fwrite($fp, $data);
                }
                fclose($fp);
                fclose($putdata);

                $success = true;
                break;
            default:
                break;
        }
        if ($success) {
            $params = (array) $this->params();
            $params['url'] = $uriRoot . '/' . $target;
            $params['path'] = $pathRoot . '/' . $target;

            $row = $this->model($this->modelName)->createRow($params);
            $row->save();
            $result = array(
                'status'    => 1,
                'data'      => $row['id'],
            );
        } else {
            $result = array(
                'status'    => 0,
            );
        }

        return $result;
    }

    /**
     * Deletes a doc
     *
     * @return array
     */
    public function deleteAction()
    {
        $response   = array();
        $id         = $this->params('id');
        $result     = $this->model($this->modelName)->update(
            array('time_deleted' => time(), 'active' => 0),
            array('id' => $id)
        );
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }
}