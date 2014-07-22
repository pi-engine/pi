<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-18
 * Time: 上午11:46
 */

namespace Module\Test\Controller\Admin;


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
        $model = $this->getModel('user');
        //var_dump($model);exit;
        $select = $model->select();
        $rowset = $model->selectWith($select);
        $pages = array();
        foreach ($rowset as $row) {
            $pages[] = array(
                'id' => $row['id'],
                'username' => $row['username'],
                'content'   =>  $row['content'],
                'flag' => $row['flag'],
            );
        }

        $count = $model->count();
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

        $this->view()->setTemplate('test-list');
    }
}