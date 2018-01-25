<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

use Pi\Form\Form as BaseForm;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class PageController extends ActionController
{
    /**
     * List pages
     */
    public function indexAction()
    {
        $page = $this->params('p', 5);
        $flag = $this->params('f', 0);

        $limit  = $this->config('item_per_page') ?: 10;
        $offset = (int)($page - 1) * $limit;
        $model  = $this->getModel('page');
        $select = $model->select()
            ->where(['flag' => $flag])
            ->limit($limit)
            ->offset($offset)
            ->order('time_created DESC');
        $rowset = $model->selectWith($select);
        $pages  = [];
        foreach ($rowset as $row) {
            $pages[] = [
                'title' => $row['title'],
                'time'  => $row['time_created'],
                'url'   => $this->url('', [
                    'action' => 'view',
                    'id'     => $row['id'],
                ]),
            ];
        }

        $count     = $model->count(['flag' => $flag]);
        $paginator = Paginator::factory($count, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'page_param'  => 'p',
                'total_param' => 't',
                'params'      => [
                    'f' => $flag,
                ],
            ],
        ]);
        $this->view()->assign([
            'paginator' => $paginator,
            'pages'     => $pages,
        ]);

        $this->view()->setTemplate('page-list');
    }

    /**
     * For page render
     */
    public function viewAction()
    {
        $id = _get('id');

        $row            = $this->getModel('page')->find($id);
        $page           = $row->toArray();
        $page['module'] = $this->getModule();

        $form = new BaseForm;
        $form->add(
            [
                'name' => 'tag',
                'type' => 'tag',
            ]
        );
        $this->view()->assign('form', $form);
        $this->view()->assign('page', $page);
        $this->view()->setTemplate('page-content');
    }
}