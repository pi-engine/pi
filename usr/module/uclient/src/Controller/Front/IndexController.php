<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Uclient\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Placeholder controller
 *
 * @author Taiwen Jiang
 */
class IndexController extends ActionController
{
    public function indexAction()
    {
        $list = Pi::service('avatar')->getList(array(3, 1, 2));
        foreach ($list as $uid => $avatar) {
            //echo '<p>' . $uid . '<br />';
            echo $avatar;
        }
        //var_dump($list);
    }

}
