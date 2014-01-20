<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Tag\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;

/**
 * Tag cases controller
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
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
        $tag        = _get('tag');
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

        $this->view()->setTemplate('list');
    }

    /**
     * Get modules
     *
     * @param string $module
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
     *
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
     * @param string $tag
     * @param $modules
     * @param string $type
     *
     * @return int
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

    public function importAction()
    {
        $tagModel   = $this->getModel('tag');
        $linkModel  = $this->getModel('link');
        $statsModel = $this->getModel('stats');

        // Flush
        $tagModel->delete(array());
        $linkModel->delete(array());
        $statsModel->delete(array());
        for ($i = 1; $i < 50; $i++) {
            $postfix = $i % 2;
            $item    = rand(1, 15);
            $tag     = 'Test_tag' . ($postfix + 1);
            $time    = time() - rand(1, 360000);
            $type    = '';
            $module  = 'article';
            Pi::api('api', 'tag')->add($module, $item, $type, $tag, $time);
            $module = 'video';
            Pi::api('api', 'tag')->add($module, $item, $type, $tag, $time);
        }

        $this->jump(array('action' => 'list'));
    }
}
