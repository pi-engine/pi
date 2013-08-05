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
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Expression;

/**
 * Audit controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AuditController extends ActionController
{
    /**
     * List of audit logs
     *
     * @return void
     */
    public function indexAction()
    {
        $limit = (int) $this->params('count', 20);
        $page = $this->params('p', 1);

        $model = Pi::model('audit');
        $offset = (int) ($page - 1) * $limit;
        $select = $model->select()->where(array())->order('id DESC')
            ->offset($offset)->limit($limit);
        $rowset = $model->selectWith($select);

        $select = $model->select()
            ->columns(array('count' => new Expression('count(*)')));
        $count = (int) $model->selectWith($select)->current()->count;

        $paginator = Paginator::factory($count);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()
                ->getMatchedRouteName(),
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
