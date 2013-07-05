<?php
/**
 * System audit controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Zongshu Lin
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Expression;

class AuditController extends ActionController
{
    public function indexAction()
    {
        $limit = (int) $this->params('count', 20);
        $page = $this->params('p', 1);

        $model = Pi::model('audit');
        $offset = (int) ($page - 1) * $limit;
        $select = $model->select()->where(array())->order('id DESC')->offset($offset)->limit($limit);
        $rowset = $model->selectWith($select);

        $select = $model->select()->columns(array('count' => new Expression('count(*)')));
        $count = (int) $model->selectWith($select)->current()->count;

        $paginator = Paginator::factory($count);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'       => $this->getModule(),
                'controller'   => 'audit',
                'count'        => $limit,
            ),
        ));

        $this->view()->assign('count', $limit);
        $this->view()->assign('items', $rowset->toArray());
        $this->view()->assign('paginator', $paginator);
        //$this->view()->setTemplate('audit-list');
    }
}
