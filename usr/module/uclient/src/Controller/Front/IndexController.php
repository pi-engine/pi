<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Uclient\Controller\Front;

use Pi;
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
        return $this->redirect()->toUrl(Pi::user()->getUrl('profile'));
    }

}
