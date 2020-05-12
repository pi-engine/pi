<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Laminas\Db\Sql\Expression;


class IndexController extends ActionController
{
    /**
     * Default action if none provided
     */
    public function indexAction()
    {
        return $this->redirect()->toRoute('', ['action' => 'top']);
    }

    /**
     * List hot tags
     */
    public function topAction()
    {
        $page    = $this->params('page', 1);
        $module  = $this->params('m');
        $limit   = (int)$this->config('item_per_page');
        $offset  = (int)($page - 1) * $limit;
        $modules = $this->getModules();

        $tags = Pi::service('tag')->top($limit, $module, null, $offset);
        array_walk($tags, function (&$tag) use ($module) {
            $tag['url'] = Pi::service('tag')->url($tag['term'], $module ?: '');
        });
        if ($module) {
            $modelStats = $this->getModel('stats');
            $select     = $modelStats->select()
                ->where(['module' => $module])
                ->columns([
                    'count' => new Expression('COUNT(DISTINCT `term`)'),
                ]);
            $row        = $modelStats->selectWith($select)->current();
            $count      = (int)$row['count'];
        } else {
            $count = $this->getModel('tag')->count();
        }

        $paginator = Paginator::factory($count, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'params' => [
                    'm' => $module,
                ],
            ],
        ]);
        $this->view()->assign([
            'paginator' => $paginator,
            'modules'   => $modules,
            'm'         => $module,
            'tags'      => $tags,
        ]);
        $this->view()->setTemplate('list-top');
    }

    /**
     * List new tags
     */
    public function newAction()
    {
        $page    = $this->params('page', 1);
        $module  = $this->params('m');
        $limit   = (int)$this->config('item_per_page');
        $offset  = (int)($page - 1) * $limit;
        $modules = $this->getModules();

        if ($module) {
            $modelStats = $this->getModel('stats');
            $select     = $modelStats->select()
                ->where(['module' => $module])
                ->columns([
                    'count' => new Expression('COUNT(DISTINCT `term`)'),
                ]);
            $row        = $modelStats->selectWith($select)->current();
            $count      = (int)$row['count'];
        } else {
            $count = $this->getModel('tag')->count();
        }

        $model  = $this->getModel('link');
        $select = $model->select();
        $select->columns([
            'term',
            'time_add' => new Expression('MIN(time)'),
        ]);
        $select->group('term');
        $select->order(['time_add DESC', 'order ASC']);
        $select->limit($limit)->offset($offset);
        $rowset = $model->selectWith($select);
        $tags   = [];
        foreach ($rowset as $row) {
            $tags[] = [
                'term' => $row['term'],
                'time' => _date($row['time_add']),
                'url'  => Pi::service('tag')->url($row['term'], $module ?: ''),
            ];
        }

        $paginator = Paginator::factory($count, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'params' => [
                    'm' => $module,
                ],
            ],
        ]);
        $this->view()->assign([
            'paginator' => $paginator,
            'modules'   => $modules,
            'm'         => $module,
            'tags'      => $tags,
        ]);
        $this->view()->setTemplate('list-new');
    }

    /**
     * List recent tagged contents
     */
    public function linkAction()
    {
        $page    = $this->params('page', 1);
        $module  = $this->params('m');
        $limit   = (int)$this->config('item_per_page');
        $offset  = (int)($page - 1) * $limit;
        $modules = $this->getModules();

        $count = Pi::service('tag')->getCount('', $module, null);
        $list  = Pi::service('tag')->getList('', $module, null, $limit, $offset);
        array_walk($list, function (&$tag) use ($module) {
            $tag['url'] = Pi::service('tag')->url($tag['term'], $module ?: '');
        });

        $content = [];
        $batches = [];
        foreach ($list as $item) {
            $batches[$item['module']][$item['type']][$item['item']][] = $item['term'];
        }
        $vars = ['id', 'title', 'link', 'time'];
        foreach ($batches as $m => $mData) {
            foreach ($mData as $t => $tData) {
                $content[$m . '-' . $t] = Pi::service('module')->content(
                    $vars,
                    [
                        'module' => $m,
                        'type'   => $t,
                        'id'     => array_keys($tData),
                    ]
                );
            }
        }

        $links = [];
        array_walk($list, function ($item) use ($modules, $content, &$links) {
            $key = $item['module'] . '-' . $item['type'];
            if (isset($content[$key]) && isset($modules[$item['module']])) {
                $found = false;
                foreach ($content[$key] as $data) {
                    if ($data['id'] == $item['item']) {
                        $item['item'] = $data;
                        $found        = true;
                        break;
                    }
                }
                if ($found) {
                    $item['module'] = $modules[$item['module']];
                    $links[]        = $item;
                }
            }
        });

        $paginator = Paginator::factory($count, [
            'limit'       => $limit,
            'page'        => $page,
            'url_options' => [
                'params' => [
                    'm' => $module,
                ],
            ],
        ]);
        $this->view()->assign([
            'paginator' => $paginator,
            'modules'   => $modules,
            'm'         => $module,
            'links'     => $links,
        ]);
        $this->view()->setTemplate('link');
    }

    /**
     * Delete tag
     */
    public function deleteAction()
    {
        $module = $this->params('m');
        $tag    = $this->params('tag', '');
        $from   = $this->params('from', 'top');

        Pi::model('tag', 'tag')->delete(['term' => $tag]);
        Pi::model('link', 'tag')->delete(['term' => $tag]);
        Pi::model('stats', 'tag')->delete(['term' => $tag]);

        $this->redirect()->toRoute('', ['action' => $from, 'm' => $module]);
    }

    /**
     * Get modules
     *
     * @return array
     */
    protected function getModules()
    {
        $list = Pi::registry('modulelist')->read();

        $modules    = [];
        $modelStats = $this->getModel('stats');
        $select     = $modelStats->select()->columns([
            'module' => new Expression('distinct module'),
        ]);
        $rowset     = $modelStats->selectWith($select);
        foreach ($rowset as $row) {
            if (isset($list[$row['module']])) {
                $modules[$row['module']] = $list[$row['module']]['title'];
            }
        }

        return $modules;
    }
}
