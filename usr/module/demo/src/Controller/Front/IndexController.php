<?php
/**
 * Demo index controller
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
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\Demo
 * @version         $Id$
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

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

        // Specify template, otherwise template will be set up as {controller}-{action}
        $this->view()->setTemplate('demo-index');
    }

    /**
     * Demo for full usage of pagination with ArrayAdapter, resource consumed!
     */
    public function pageAction()
    {
        $page = $this->params('p', 5);
        $flag = $this->params('f', 0);

        //$offset = ($page - 1) * $this->config('item_per_page');
        $limit = $this->config('item_per_page');
        $model = $this->getModel('page');
        //$select = $model->select()->where(array('flag' => $flag))->order('id')->offset($offset)->limit($limit);
        $select = $model->select()->where(array('flag' => $flag))->order('id');
        $rowset = $model->selectWith($select);
        $pages = array();
        foreach ($rowset as $row) {
            $pages[] = $row;
        }

        //$data = $rowset->toArray();
        $paginator = \Pi\Paginator\Paginator::factory($pages);
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'index',
                'action'        => 'page',
                'f'             => $flag,
            ),
            // Or use a URL template to create URLs
            //'template'      => '/url/p/%page%/t/%total%',

        ));
        $this->view()->assign('paginator', $paginator);
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
        $select = $model->select()->where(array('flag' => $flag))->order('id')->offset($offset)->limit($limit);
        //$select = $model->select()->where(array('flag' => $flag))->order('id');
        $rowset = $model->selectWith($select);
        $items = array();
        foreach ($rowset as $row) {
            $items[] = $row;
        }

        //$data = $rowset->toArray();
        $select = $model->select()->columns(array('count' => new \Zend\Db\Sql\Predicate\Expression('count(*)')))->where(array('flag' => $flag));
        $count = $model->selectWith($select)->current()->count;

        $paginator = \Pi\Paginator\Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'index',
                'action'        => 'simple',
                'f'             => $flag,
            ),
            // Or use a URL template to create URLs
            //'template'      => '/url/p/%page%/t/%total%',

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
        $content = sprintf(__('<br />No template rendering.<br />Test is now at %s'), __METHOD__);

        Pi::service('event')->trigger('user_call', __('Triggered data from Demo module'), 'demo');
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
