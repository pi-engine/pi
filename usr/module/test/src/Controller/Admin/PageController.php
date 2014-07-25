<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-18
 * Time: 上午11:46
 */

namespace Module\Test\Controller\Admin;


use pi;
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
        $page = $this->params('p', 1);
        $flag = $this->params('f', 0);

        $limit  = $this->config('item_per_page') ?: 10;
        $offset = (int) ($page - 1) * $limit;
        $model = $this->getModel('user');
        $select = $model->select()
            ->where(array('flag' => $flag))
            ->limit($limit)
            ->offset($offset)
            ->order('id DESC');
        $rowset = $model->selectWith($select);
        $pages = array();
        foreach ($rowset as $row) {
            $pages[] = array(
                'username' => $row['username'],
                'content'   =>  $row['content'],
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
        $test = $this->config('display');
        if($test){
            $model = $this->getModel('user');
        }
        $this->view()->assign(array(
            'paginator'     => $paginator,
            'pages'         => $pages,
        ));
        $this->view()->setTemplate('test-list');
    }
}