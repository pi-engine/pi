<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Controller\Admin;

use Module\System\Controller\Front\JumpController as JumpControllerFront;

/**
 * Page jump
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class JumpController extends JumpControllerFront
{
    public function permissionException()
    {
        return true;
    }

    /**
     * Transition page jump
     */
    public function indexAction()
    {
        parent::indexAction();
        $this->view()->setTemplate('jump', '', 'front');
    }
}
