<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

//use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Form\Form as BaseForm;
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
        $offset = (int) ($page - 1) * $limit;
        $model = $this->getModel('page');
        $select = $model->select()
            ->where(array('flag' => $flag))
            ->limit($limit)
            ->offset($offset)
            ->order('time_created DESC');
        $rowset = $model->selectWith($select);
        $pages = array();
        foreach ($rowset as $row) {
            $pages[] = array(
                'title' => $row['title'],
                'time'  => $row['time_created'],
                'url'   => $this->url('', array(
                        'action'    => 'view',
                        'id'        => $row['id'],
                    )),
            );
        }

        $count = $model->count(array('flag' => $flag));
        $paginator = Paginator::factory($count, array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'page_param'    => 'p',
                'total_param'   => 't',
                'params'        => array(
                    'f'             => $flag,
                ),
            )
        ));
        $this->view()->assign(array(
            'paginator'     => $paginator,
            'pages'         => $pages,
        ));

        $this->view()->setTemplate('page-list');
    }

    /**
     * For page render
     */
    public function viewAction()
    {
        $id = _get('id');

        $row = $this->getModel('page')->find($id);
        $page = $row->toArray();
        $page['module'] = $this->getModule();

        $form = new BaseForm;
        $form->add(
            array(
                'name'  => 'tag',
                'type'  => 'tag',
            )
        );
        $this->view()->assign('form', $form);
        $this->view()->assign('page', $page);
        $this->view()->setTemplate('page-content');
    }
}