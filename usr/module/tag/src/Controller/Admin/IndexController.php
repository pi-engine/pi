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
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\Tag\Controller\Admin;

use Pi\Mvc\Controller\ActionController;
use Module\Tag\Form\SearchForm;
use Module\Tag\Form\SearchFilter;
//use Module\Tag\Service;
use Module\Tag\Form;
use Zend\Db\Sql\Expression;
use Pi\Paginator\Paginator;
use Pi;

class IndexController extends ActionController
{
    protected function getExistModule()
    {
        $statsModel = $this->getModel('stats');
        $moduleArray = array();
        $modelStats = $this->getModel('stats');
        $select = $statsModel->select()->columns(array('module' => new Expression('distinct module')));
        $data = $modelStats->selectWith($select);
        foreach ($data as $row) {
            $moduleArray[] = $row->module;
        }
        return $moduleArray;
    }

    protected function getTagName($tagIds)
    {
        $tagModel = $this->getModel('tag');
        $result = array();
        $resultAsset = array();
        $tagIds = is_scalar($tagIds) ? array ($tagIds) : $tagIds;
        if (!empty($tagIds)) {
            $select = $tagModel->select()->where(array('id' => $tagIds));
            $resultAsset = $tagModel->selectWith($select);
        }

        foreach ($resultAsset as $asset) {
            $result[$asset->id] = $asset->term;
        }

        return $result;
    }

    /**
     * Default action if none provided
     * Partnumber admin
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('admin', array('controller' => 'index', 'action' => 'list'));
    }

    /**
     * List tags
     */
    public function listAction()
    {
        // Get website module name
        $modelStats = $this->getModel('stats');
        $modelTag = $this->getModel('tag');
        $select = $modelStats->select()->columns(array('module' => new Expression('distinct module')));
        $data = $modelStats->selectWith($select);
        foreach ($data as $row) {
            $moduleArray[] = $row['module'];
        }
        $page = intval($this->params('page', 1));
        //var_dump($page);
        $module = $this->params('m', null);
        $module = $module != '' ? $module : null;
        $form = $this->getForm();
        $limit = (int) $this->config('item_per_page');
        $offset = (int) ($page - 1) * $limit;
        $items = array();
        if (null === $module) {
            // Get datas from tag table
            $select = $modelTag->select()->where(array())->order(array('count DESC'))->offset($offset)->limit($limit);
            $items = $modelTag->selectWith($select)->toArray();
        } else {
            // Get datas from stats table
            $modelStats = $this->getModel('stats');
            $select = $modelStats->select()->where(array('module' => $module))->order(array('count DESC'))->offset($offset)->limit($limit);
            $rowset = $modelStats->selectWith($select)->toArray();

            foreach ($rowset as $row) {
                $select = $modelTag->select()->where(array('id' => $row['tag']));
                $items[] = array(
                    'id'    => $row['tag'],
                    'term'  => $modelTag->selectWith($select)->current()->term,
                    'count' => $row['count'],
                );
            }
        }
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
            'curModule'     => $module,
            'datas'         => $items,
        ));
        $this->view()->setTemplate('list');
    }

    /**
     * Show specific module of tag.
     */
    public function linkListAction()
    {
        $limit      = (int) $this->config('item_per_page');
        $page       = _get('page') ? (int) _get('page') : 1;
        $offset     = (int) ($page - 1) * $limit;
        $moduleName = _get('m');

        $model = $this->getModel('link');
        $where = array();
        if ($moduleName) {
            $where['module'] = $moduleName;
        }
        $select = $model->select()->where($where);
        $select->limit($limit);
        $select->offset($offset);
        $rowset = $model->selectWith($select);
        $count = $model->count($where);

        $links = array();
        foreach ($rowset as $row) {
            $links[] = array(
                'tag'       => $row->tag,
                'tag_link'  => '',
                'item'      => $row->item,
                'item_link' => '',
                'module'    => $row->module,
                'type'      => $row->type,
                'time'      => $row->time ? _date($row->time) : 0,

            );
            $tagIds[] = $row->tag;
        }

        $tagTitle  = $this->getTag($tagIds);

        foreach ($links as &$link) {
            $moduleMeta = Pi::service('module')->loadMeta($link['module']);
            $variables  = array('title', 'id');
            $conditions = array(
                'module' => $link['module'],
                'id'     => $link['item']
            );
            $content = Pi::service('module')->
                content($variables, $conditions);
            $content = $this->canonizeContent($content);

            $link['tag_link'] = $this->url('',
                array(
                    'action' => 'detail',
                    'id' => $link['tag']
                )
            );
            $link['tag']       = $tagTitle[$link['tag']];
            $link['item_link'] = $content[$link['item']]['link'];
            $link['item']      = $content[$link['item']]['title'];
            $link['module']    = $moduleMeta['meta']['title'];
        }

        $paginator = Paginator::factory(intval($count), array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'params' => array(
                    'm'      => $moduleName
                )
            )
        ));

        $this->view()->assign(array(
            'paginator'  => $paginator,
            'links'      => $links,
            'modules'    => $this->getModules(),
            'count'      => $count,
            'cur_module' => $moduleName
        ));

        $this->view()->setTemplate('link-list');
    }

    /**
     * Delete website tag.
     */
    public function deleteAction()
    {
        $id = intval($this->params('id'));
        $search = $this->params('search');
        $tagName = $this->params('name');
        $verify = $this->params('verify', 'n');

        // Delete from link table.
        $modelLink = $this->getModel('link');
        $modelLink->delete(array('tag' => $id));

        // Delete from stats table.
        $modelStats = $this->getModel('stats');
        $modelStats->delete(array('tag' => $id));

        // Delete from tag table
        $modelTag = $this->getModel('tag');
        $modelTag->delete(array('id' => $id));

        $this->view()->setTemplate(false);

        // Set link
        if ($search == 'y') {
            return $this->redirect()->toRoute('admin', array('action' => 'search', 'name' => $tagName, 'search' => 'y'));
        } elseif ($verify == 'y') {
            return $this->redirect()->toRoute('admin', array('action' => 'verify'));
        } else {
            return $this->redirect()->toRoute('admin', array('action' => 'list'));
        }
    }

    /**
     * Delete module of tag.
     *
     */
    public function moduleDeleteAction()
    {
        $id = intval($this->params('id', null));
        $module = $this->params('m');

        // Delete from stats table
        $modelStats = $this->getModel('stats');
        $count = $modelStats->select(array('tag' => $id, 'module' => $module))->count();

        $modelStats->delete(array('tag' => $id, 'module' => $module));

        // Delete from link table.
        $modelLink = $this->getModel('link');
        $modelLink->delete(array('tag' => $id, 'module' => $module));

        // Delete from tag table.
        $modelTag =$this->getModel('tag');
        $modelTag->update(array('count' => new Expression("count - {$count}")), array('id' => $id));

        $this->view()->setTemplate(false);

        return $this->redirect()->toRoute('admin', array('action' => 'list', 'm' => $module));

    }

    /**
     * Static tag
     */
    public function statsAction()
    {
        // Static top 10 tag.
        $limit = (int) $this->config('item_per_page');;
        $offset = 0;
        $modelTag = $this->getModel('tag');
        $select = $modelTag->select()->where(array())
            ->order(array('count DESC'))
            ->offset($offset)
            ->limit($limit);
        $topTag = $modelTag->selectWith($select)->toArray();

        // Static top10 new tag.
        $modelLink = $this->getModel('link');
        $select = $modelLink->select()->where(array())
            ->order(array('time DESC'))
            ->group('tag')
            ->offset($offset)
            ->limit($limit);
        $resultLinkAsset = $modelLink->selectWith($select);

        // Set new tag data
        foreach ($resultLinkAsset as $asset) {
            $newTags[] = array(
                $asset->tag     => '',
                'time'          => date("Y-m-d", $asset->time),
                'tagId'         => $asset->tag,
            );
            $tagIds[]           = $asset->tag;
        }

        // Get tag name
        $tagNames = $this->getTagName($tagIds);
        foreach ($newTags as $index => $newTag) {
            $newTags[$index][$newTag['tagId']] = $tagNames[$newTag['tagId']];
        }

        $this->view()->assign(array(
            'topTag'        => $topTag,
            'newestTag'     => $newTags,
        ));
        $this->view()->setTemplate('stats');
    }

    /**
     * Verify invalid link of tag.
     */
    public function verifyAction()
    {
        // Verify invalid links
        $model = $this->getModel('link');
        $select = $model->select()->where(array());
        $rowset = $model->selectWith($select)->toArray();
        // Conversion item id to item name
        $items = array();
        foreach ($rowset as $row) {
            // Get item name.
            $variables = array('title');
            $conditions['id'] = $row['item'];
            $conditions['module'] = $row['module'];
            $conditions['type'] = $row['type'];
            $datas = Pi::service('module')->content($variables, $conditions);
            $itemName = $datas[$row['item']]['title'];
            $row['itemName'] = $itemName;
            // Conversion tag id to tag term.
            $modelTag = $this->getModel('tag');
            $select = $modelTag->select()->where(array('id' => $row['tag']));
            $term = $modelTag->selectWith($select)->current();
            $row['term'] = $term['term'];
            if (empty($datas[$row['item']]['title']) || empty($term['term'])) {
                $items[] = $row;
            }
        }

        // Verify isolated tag
        $modelTag = $this->getModel('tag');
        $select = $modelTag->select()->where(array('count' => 0));
        $invalidTag = $modelTag->selectWith($select)->toArray();

        $this->view()->assign(array(
            'invalidTag'   => $invalidTag,
            'items'         => $items,
        ));

        $this->view()->setTemplate('verify');
    }

    /**
     * Search tag.
     */
    public function searchAction()
    {
        $module = $this->params('m', null);
        if ('' == $module) {
            $module = null;
        }
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
        $page = (int) $this->params('page', 1);
        $limit = (int) $this->config('item_per_page');
        $offset = (int) ($page - 1) * ((int) $this->config('item_per_page'));
        $select = $modelTag->select();
        $select->where->like('term', "%{$tagName}%");
        $select->order(array('count DESC'));
        $select->offset($offset)->limit($limit);
        $rowset = $modelTag->selectWith($select);
        $items = $rowset->toArray();

        if (count($items) == 0) {
            $this->view()->assign('find', 'n');
        } else {
            $this->view()->assign('find', 'y');
        }

        // Set paginator parameters
        $select = $modelTag->select();
        $select->where->like('term', "%{$tagName}%");
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

    /**
     * Delete invalid link.
     */
    public function linkDeleteAction()
    {
        $linkId = $this->params('id');
        $modelLink = $this->getModel('link');

        // Get tag id
        $select = $modelLink->select()->where(array('id' => $linkId));
        $rowset = $modelLink->selectWith($select)->current();
        $tagId = $rowset->tag;
        $moduleName = $rowset->module;

        // Update tag from tag table
        $modelTag = $this->getModel('tag');
        $select = $modelTag->select()->where(array('id' => $tagId));
        $rowset = $modelTag->selectWith($select)->toArray();
        foreach ($rowset as $row) {
            if($row['count'] != 0) {
                $modelTag->update(array('count' =>  new Expression('count - 1')), array('id' => $row['id']));
            }
        }

        // Update tag from stats table
        $modelStat = $this->getModel('stats');
        $select = $modelStat->select()->where(array('tag' => $tagId, 'module' => $moduleName));
        $rowset = $modelStat->selectWith($select)->toArray();

        foreach ($rowset as $row) {
            if ($row['count'] > 1) {
                $modelStat->update(array('count' => new Expression('count - 1')), array('id' => $row['id']));
            } elseif($row['count'] == 1) {
                $modelStat->delete(array('id' => $row['id']));
            }
        }

        // Delete invalid link from link table
        $modelLink->delete(array('id' => $linkId));

        // Go to verify page
        return $this->redirect()->toRoute('admin', array('controller' => 'index', 'action' => 'verify'));
    }

    public function detailAction()
    {

        $tag        = _get('id');
        $type       = _get('type');
        $limit      = (int) $this->config('item_per_page');
        $page       = _get('page') ? (int) _get('page') : 1;
        $offset     = (int) ($page - 1) * $limit;
        $moduleName = _get('m');

        $modules = $this->getModules($moduleName);
        if (!is_numeric($tag)) {
            $tagId = $this->getTagId($tag);
        } else {
            $tagId  = (int) $tag;
            $result = $this->getTag($tag);
            $tag    = $result[$tag];
        }

        $list = $this->getList(
            $tagId,
            array_keys($modules),
            $type,
            $limit,
            $offset
        );

        $count = $this->getCount(
            $tagId,
            array_keys($modules),
            $type
        );

        $paginator = Paginator::factory(intval($count), array(
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => array(
                'params' => array(
                    'tag'    => $tag,
                    'type'   => $type,
                    'm'      => $moduleName
                )
            )
        ));

        $this->view()->assign(array(
            'paginator'  => $paginator,
            'list'       => $list,
            'modules'    => $this->getModules(),
            'tag'        => $tag,
            'tag_id'     => $tagId,
            'count'      => $count,
            'cur_module' => $moduleName
        ));

        $this->view()->setTemplate('detail');
    }

    /**
     * Get modules
     *
     * @return array
     */
    protected function getModules($module = '')
    {
        $activeModules = Pi::registry('modulelist')->read('active');
        if (isset($activeModules[$module])) {
            $modules[$module] = $activeModules[$module]['title'];
            return $modules;
        }
        $modules    = array();
        $modelStats = $this->getModel('stats');
        $select     = $modelStats->select()->columns(
            array('module' => new Expression('distinct module')
            ));
        $rowset = $modelStats->selectWith($select);
        foreach ($rowset as $row) {
            if (in_array($row->module, array_keys($activeModules))) {
                $modules[$row->module] = $activeModules[$row->module]['title'];
            }
        }

        return $modules;
    }

    /**
     * Get list
     *
     * @param $tag
     * @param null $modules
     * @param null $type
     * @param int $limit
     * @param int $offset
     * @return array
     */
    protected function getList(
        $tag,
        $modules = null,
        $type    = null,
        $limit   = 0,
        $offset  = 0
    ) {
        $list = array();
        $where = array(
            'tag' => $tag,
        );
        if ($modules) {
            $where['module'] = $modules;
        }
        if ($type) {
            $where['type'] = $type;
        }

        $model  = $this->getModel('link');
        $select = $model->select()->where($where);
        $select->order('time desc');
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $rowset  = $model->selectWith($select)->toArray();
        $tagIds  = array();
        foreach ($rowset as $row) {
            $moduleMeta = Pi::service('module')->loadMeta($row['module']);
            $variables  = array('title', 'id');
            $conditions = array(
                'module' => $row['module'],
                'id'     => $row['item']
            );
            $content = Pi::service('module')->content($variables, $conditions);
            $content = $this->canonizeContent($content);
            $list[] = array(
                'tag'       => $row['tag'],
                'item'      => $content[$row['item']]['title'],
                'time'      => $row['time'] ? _date($row['time']) : 0,
                'item_link' => $content[$row['item']]['link'],
                'module'    => $moduleMeta['meta']['title']
            );
            $tagIds[]  = $row['tag'];
        }
        if ($rowset) {
            // Get tag title
            $tagTitle  = $this->getTag($tagIds);
            foreach ($list as &$val) {
                $list['tag'] = $tagTitle[$val['tag']];
            }
        }

        return $list;
    }

    /**
     * Get count
     *
     * @param $tag
     * @param $modules
     * @param string $type
     * @return bool|int|\Zend\Db\ResultSet\ResultSet
     */
    protected function getCount($tag, $modules, $type = '')
    {
        $modules = (array) $modules;
        $where   = array(
            'tag' => $tag,
            'module' => $modules
        );
        if ($type) {
            $where['type'] = $type;
        }

        $count = $this->getModel('link')->count($where);

        return $count;
    }

    /**
     * Get tag title
     *
     * @param $ids
     * @return array
     */
    protected function getTag($ids)
    {
        $result = array();
        if (!$ids) {
            return $result;
        }

        if (!is_array($ids)) {
            $ids = (array) $ids;
        }

        $model  = $this->getModel('tag');
        $where  = array('id' => $ids);
        $select = $model->select()->where($where);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $result[$row['id']] = $row['term'];
        }

        return $result;
    }

    /**
     * Get tag id
     *
     * @param $tag
     * @return int
     */
    protected function getTagId($tag)
    {
        $result = 0;
        $row = $this->getModel('tag')->find($tag, 'term');
        if ($row && $row->id) {
            $result = $row->id;
        }

        return $result;
    }

    /**
     * Canonize content
     *
     * @param $content
     * @return array
     */
    protected function canonizeContent($content)
    {
        $result = array();
        foreach ($content as $row) {
            $result[$row['id']] = $row;
            unset($result[$row['id']]['id']);
        }

        return $result;
    }

    /**
     * Test module interface
     */
    public function testAction()
    {
        //$module = 'article';
        //$item = 16;
        //$tags = 'TAG2';
        //$type = 'typea';
        //$tags = array('TAG1', 'TAG2', 'TAG3', 'TAG4', 'TAG6', 'TAG7', 'TAG8', 'TAG9','TAG10', 'TAG11','TAG12', 'TAG13');
        //$tags = array('test1', 'test2', 'test3');
        //$tags = array('TAG2', 'TAG3', 'TAG4', 'TAG16');
        //$tags = array('t3', 't5');
        //$tags = array('A', 'B', 'AAC', 'AAD', 'AAE', 'AAF', 'AAG', 'AAH', 'AAK', 'AAH');
        //$time = time();
        //$tags = array('A', 'B', 'C', 'D', 'E', 'F');
        //$tags = array('LED');
        //Pi::service('tag')->add($module, $item, null, $tags);
        //Pi::service('tag')->update($module, $item, null, $tags);
        //$re = Pi::service('tag')->getList($module, $tags);
        //$re = Pi::service('tag')->getlist($module, $tags);
        //d($re);
        //Pi::service('tag')->add($module, $item, null, $tags);
        //Pi::service('api')->partnumber->update($module, $item, null, $tags, $time);
        //$re = Pi::service('tag')->get($module, $item, $type);
        //d($re);
        //$re = Pi::service('api')->partnumber->getUrl($module, $item, $type);
        //$re =  Pi::service('api')->partnumber->getList($module, array('TAG22'));
        //$re =  Pi::service('api')->partnumber->getCount($module, 'TAG23');
        //$items = array('314388', 0, 314386, 1);
        //$items = 31438;
        //$re =  Pi::service('api')->tag->getTag($module, $items, $type = null);
        //d($re);
        $this->view()->setTemplate(false);
        //$this->view()->setTemplate('false');
    }
    /**
     *
     */
    public function getForm()
    {
        $form = new SearchForm('searchform');
        $form->setAttributes(array(
            'action'    => $this->url('admin', array('controller' => 'index', 'action' => 'search')),
            'method'    => 'post',
            'class'     => 'well form-inline',
        ));

        return $form;
    }
}
