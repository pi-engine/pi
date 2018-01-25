<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

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
        $limit = (int)$this->params('count', 20);
        $page  = $this->params('p', 1);

        $model  = Pi::model('audit');
        $offset = (int)($page - 1) * $limit;
        $select = $model->select()->where([])->order('id DESC')
            ->offset($offset)->limit($limit);
        $rowset = $model->selectWith($select);

        /*
        $select = $model->select()
            ->columns(array('count' => new Expression('count(*)')));
        $count = (int) $model->selectWith($select)->current()->count;
        */
        $count = $model->count();

        $paginator = Paginator::factory($count, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'page_param' => 'p',
                'params'     => [
                    'count' => $limit,
                ],
            ],
        ]);

        $this->view()->assign('count', $limit);
        $this->view()->assign('items', $rowset->toArray());
        $this->view()->assign('paginator', $paginator);
        //$this->view()->setTemplate('audit-list');
    }
}
