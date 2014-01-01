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
class DocController extends ApiController
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
        $params = (array) $this->params();
        $id = Pi::api('doc', $this->module)->upload(
            $params,
            $this->request->getMethod()
        );

        if (!$id) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        } else {
            $response = array(
                'status'    => 1,
                'data'      => $id
            );
        }

        return $response;
    }

    /**
     * Deletes a doc
     *
     * @return array
     */
    public function deleteAction()
    {
        $response   = array(
            'status'    => 1,
        );

        $id     = $this->params('id');
        $result = Pi::api('doc', $this->module)->delete($id);
        if (!$result) {
            $response = array(
                'status'    => 0,
                'message'   => 'Operation failed.'
            );
        }

        return $response;
    }
}