<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Front;

use Pi\Mvc\Controller\ActionController;

/**
 * Placeholder controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class IndexController extends ActionController
{
    public function indexAction()
    {
        $id = (int) _get('block');
        $list = array();
        $blockLoader = $this->view()->helper('blocks');
        if ($id) {
            $blockRender = $this->view()->helper('block');
            $block= $blockRender($id);
            if ($block) {
                $zones = $blockLoader->getZones();
                foreach ($zones as $key => $zone) {
                    $list[$key] = array($block);
                }
            }
        }
        $blockLoader->assign($list);

        $this->view()->setTemplate('block-preview');
    }
}
