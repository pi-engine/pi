<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Module\System\Controller\Front\JumpController as FrontJump;

/**
 * Page jump
 */
class JumpController extends FrontJump
{
    /**
     * Transition page jump
     */
    public function indexAction()
    {
        parent::indexAction();
        /*
        $params = Pi::service('cookie')->get('PI_JUMP', true, true);
        if (empty($params['time'])) {
            $params['time'] = 3;
        }
        if ($this->url('', array('action' => 'index')) == $params['url']) {
            $params['url'] = '';
        }
        if (empty($params['url'])) {
            $params['url'] = Pi::url('www');
        }
        $this->view()->assign($params);
        */
        $this->view()
            ->setTemplate('system:front/jump')
            ->setLayout('layout-simple');
    }
}
