<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

class IndexController extends ActionController
{
    /**
     * A test page with a couple of API demos
     */
    public function indexAction()
    {
        // Assign multiple params
        $data = array(
            'data'      => 'Pi-Zend',
            'module'    => $this->params('module'),
            'title'     => __('Demo page'),
        );
        $this->view()->assign($data);

        // Assign all route params
        $this->view()->assign('params', $this->params()->fromRoute());

        // Assign one single param
        $this->view()->assign('TheParam', 'A specific parameter');

        // Specify page head title
        $this->view()->headTitle()->prepend('Demo page');
        // Specify meta parameter
        $this->view()->headMeta()->prependName('generator', 'DEMO');

        // Specify template,
        // otherwise template will be set up as {controller}-{action}
        $this->view()->setTemplate('demo-index');
    }

    /**
     * Demo for lean usage of pagination with NullApapter
     */
    public function simpleAction()
    {
        $page = $this->params('p', 5);
        $flag = $this->params('f', 0);

        $limit = (int) $this->config('item_per_page');
        $model = $this->getModel('page');

        $offset = (int) ($page - 1) * $this->config('item_per_page');
        $select = $model->select()->where(array('flag' => $flag))
            ->order('id')->offset($offset)->limit($limit);
        $rowset = $model->selectWith($select);
        $items = array();
        foreach ($rowset as $row) {
            $items[] = $row;
        }

        //$data = $rowset->toArray();
        /*
        $select = $model->select()
            ->columns(array('count' => new Expression('count(*)')))
            ->where(array('flag' => $flag));
        */
        $count = $model->count(array('flag' => $flag));

        /*
        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'page_param'    => 'p',
            'total_param'   => 't',
            'params'        => array(
                'f'             => $flag,
            ),
            // Or use a URL template to create URLs
            //'template'      => '/url/p/__page__/t/__total__',
            'template'  => Pi::service('url')->assemble('', array(
                'p' => '__page__',
                't' => '__total__',
                'f' => $flag,
            ), true),
        ));
        */
        $paginator = Paginator::factory(intval($count), array(
            'limit' => $limit,
            'page'  => $page,
            'url_options'           => array(
                // Use router to build URL for each page
                'page_param'    => 'p',
                'total_param'   => 't',
                'params'        => array(
                    'f'             => $flag,
                ),

                // Or use a URL template to create URLs
                //'template'      => '/url/p/__page__/t/__total__',
                /*
                'template'  => Pi::service('url')->assemble('', array(
                    'p' => '__page__',
                    't' => '__total__',
                    'f' => $flag,
                ), true),
                */
            ),
        ));
        $this->view()->assign('items', $items);
        $this->view()->assign('paginator', $paginator);
    }

    /**
     * Test page for event trigger
     *
     * @return string
     */
    public function testAction()
    {
        $content = sprintf(
            __('<br />No template rendering.<br />Test is now at %s'),
            __METHOD__
        );

        Pi::service('event')->trigger(
            'user_call',
            __('Triggered data from Demo module'),
            'demo'
        );
        // Disable template
        $this->view()->setTemplate(false);

        return $content;
    }

    public function userAction()
    {
        $content = __('Test for user_call event');
        Pi::service('event')->trigger('user_call');
        $this->view()->setTemplate(false);

        return $content;
    }

    /**
     * Test for redirect plugin with demo route
     */
    public function redirectAction()
    {
        $this->redirect()->toRoute('', array('action' => 'test'));
    }

    /**
     * Test for API call
     */
    public function apiAction()
    {
        return __METHOD__;
    }
}
