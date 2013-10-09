<?php
/**
 * Tag index controller
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
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @version         $Id$
 */

namespace Module\Tag\Controller\Front;

use Pi\Mvc\Controller\ActionController;
use Module\Tag\Form\SearchForm;
use Module\Tag\Form\SearchFilter;
use Module\Tag\Service;
use Module\Tag\Form;
use Zend\Db\Sql\Expression;
use Pi;

class IndexController extends ActionController
{
    /**
    * Default action if none provided
    * Tag admin
    *
    * @return ViewModel
    */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', array('action' => 'list'));
    }

    /**
     * Show all tag of website.
     */
    public function listAction()
    {
        // Get website module name
        $modelStats = $this->getModel('stats');
        $select = $modelStats->select()->columns(array('module' => new Expression('distinct module')));
        $data = $modelStats->selectWith($select)->toArray();
        foreach ($data as $row) {
            $moduleArray[] = $row['module'];
        }

        $page = intval($this->params('p', 1));
        $module = $this->params('m', null);
        $module = $module != '' ? $module : null;
        $form = $this->getForm($module);
        $limit = (int) $this->config('item_per_page');
        $modelTag = $this->getModel('tag');
        $offset = (int) ($page - 1) * $limit;
        if ($module === null) {
            // Get datas from tag table
            $select = $modelTag->select()->where(array())->order(array('count DESC'))->offset($offset)->limit($limit);
        } else {
            // Get datas from stats table
            $modelStats = $this->getModel('stats');
            $select = $modelStats->select()->where(array('module' => $module))->order(array('count DESC'))->offset($offset)->limit($limit);
            $rowset = $modelStats->selectWith($select)->toArray();
            foreach ($rowset as $row) {
                $tagIds[] = $row['tag'];
            }
            $select = $modelTag->select()->where(array('id' => $tagIds));
        }
        $items = $modelTag->selectWith($select)->toArray();

        // Get amount tag
        if (null !== $module) {
            $select = $modelStats->select()->where(array('module' => $module))->columns(array('count' => new Expression('count(*)')));
            $count = $modelStats->selectWith($select)->current()->count;
        } else {
            $select = $modelTag->select()->where(array())->columns(array('count' => new Expression('count(*)')));
            $count = $modelTag->selectWith($select)->current()->count;
        }

        // Set paginator parameters
        $paginator = \Pi\Paginator\Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'       => $this->getModule(),
                'controller'   => 'index',
                'action'       => 'list',
                'm'            => $module,
            ),
        ));

        $this->view()->assign(array(
            'paginator'     => $paginator,
            'moduleArray'   => $moduleArray,
            'form'          => $form,
            'curModule'        => $module,
            'datas'         => $items,
        ));
        $this->view()->setTemplate('list');
    }

    /**
     * Show tag releated object.
     */
    public function detailAction()
    {
        // Get website module
        $modelStats = $this->getModel('stats');
        $select = $modelStats->select()->columns(array('module' => new Expression('distinct module')));
        $data = $modelStats->selectWith($select)->toArray();
        foreach ($data as $row) {
            $moduleArray[] = $row['module'];
        }

        $module = $this->params('m', null);
        $module = $module != '' ? $module : null;
        $tagId = $this->params('id', null);
        if ('' == $tagId) {
            $tagId = null;
        }

        $modelLink = $this->getModel('link');
        $modelTag = $this->getModel('tag');
        // Get tag name.
        $select = $modelTag->select()->where(array('id' => $tagId));
        $tagTerm = $modelTag->selectWith($select)->current()->term;

        $where = array('tag' => $tagId);
        if (null !== $module) {
            $where['module'] = $module;
        }

        // Get amount of table
        $amount = $modelLink->select($where)->count();
        // element count of page.
        $page = (int) $this->params('p', 1);
        // Get item per page
        $limit = (int) $this->config('detail_per_page');
        $offset = (int) ($page - 1) * $limit;
        // Get data from database.
        $select = $modelLink->select()->where($where)
                            ->offset($offset)
                            ->limit($limit);
        $items = $modelLink->selectWith($select)->toArray();
        // Get tag link  item name.
        $variables = array('title', 'time', 'url');
        $result = array();
        foreach ($items as $row) {
            $conditions['id'] = $row['item'];
            $conditions['module'] = $row['module'];
            $conditions['type'] = $row['type'];
            $datas = Pi::service('module')->content($variables, $conditions);
             if ($datas != null) {
                $datas[$row['item']]['module'] = $row['module'];
                $result[] = $datas[$row['item']];
            }
        }
        // Set paginator parameter.
        $paginator = \Pi\Paginator\Paginator::factory(intval($amount));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'index',
                'action'        => 'detail',
                'id'            => $tagId,
            ),
        ));

        $this->view()->setTemplate('detail');
        $this->view()->assign(array(
            'datas'         => $result,
            'tagTerm'       => $tagTerm,
            'curModule'        => $module,
            'moduleArray'   => $moduleArray,
            'tagid'         => $tagId,
            'paginator'     => $paginator,
        ));
    }

    public function getForm($module)
    {
        $form = new SearchForm('searchform');
        $form->setAttributes(array(
            'action'    => $this->url('', array('controller' => 'index', 'action' => 'search')),
            'method'    => 'post',
            'class'     => 'well form-inline',
            ));

        return $form;
    }

    /**
     * Search tag process.
     */
    public function searchAction()
    {
        $module = $this->params('m', null);
        $module = $module != '' ? $module : null;
        $modelTag = $this->getModel('tag');
        $tagName = $this->params('name', null);

        // Get data from form
        if (! isset($tagName)) {
            if (!$this->request->isPost()) {
                return $this->redirect()->toRoute('', array('action' => 'list', 'm' => $module));
            }
            $post = $this->request->getPost();
            $form = $this->getForm($module);
            $form->setData($post);
            $form->setInputFilter(new SearchFilter);
            if (!$form->isValid()) {
                return $this->redirect()->toRoute('', array('action' => 'list', 'm' => $module));
            }
            $term = $form->getData();
            $tagName =  $term['tagname'];
        }

        // Get search result
        $page = (int) $this->params('p', 1);
        $limit = (int) $this->config('item_per_page');
        $offset = (int) ($page - 1) * $limit;
        $select = $modelTag->select();
        $select->where->like('term', "%{$tagName}%");
        $select->order(array('count DESC'))->offset($offset)->limit($limit);
        $items = $modelTag->selectWith($select)->toArray();

        if (count($items) == 0) {
            $this->view()->assign('find', 'n');
        } else {
            $this->view()->assign('find', 'y');
        }

        // Set paginator parameters
        $select = $modelTag->select();
        $select->where->like('term', "%{$name}%");
        $select->columns(array('count' => new Expression('count(*)')));
        $count = $modelTag->selectWith($select)->current()->count;
        $paginator = \Pi\Paginator\Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'       => $this->getModule(),
                'controller'   => 'index',
                'action'       => 'search',
                'm'            => $module,
                'name'         => $tagName,
            ),
        ));

        $this->view()->assign(array(
            'paginator'        => $paginator,
            'tagName'          => $tagName,
            'items'            => $items,
        ));

        $this->view()->setTemplate('search');
    }
}
