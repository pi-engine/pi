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