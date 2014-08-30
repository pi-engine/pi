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


class ActivityController extends ActionController
{
    public function indexAction()
    {
        return array();
    }

    public function getAction()
    {
        $uid = _get('uid');
        $data = array(
            'uid'      => $uid,
            'title'    => 'Demo activity title',
            'contents' => 'Demo activity contents',
        );

        return $data;
    }
}
