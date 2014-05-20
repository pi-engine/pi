<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $limit      = (int) $this->config('item_per_page');
        $page       = _get('page') ? (int) _get('page') : 1;
        $offset     = (int) ($page - 1) * $limit;
        $module     = _get('m');

        $type           = null;
        $moduleTitle    = '';

        $modules = Pi::registry('modulelist')->read();
        if ($module && !isset($modules[$module])) {
            $module = '';
        }
        if ($module) {
            $moduleTitle = $modules[$module]['title'];
        }

        $paginator = null;
        $list = array();
        $count = Pi::service('tag')->getCount($tag, $module, $type);
        if ($count) {
            $items = Pi::service('tag')->getList(
                $tag,
                $module,
                $type,
                $limit,
                $offset
            );

            $content = array();
            $batches = array();
            foreach ($items as $item) {
                //$key = $item['module'] . '-' . $item['type'];
                $batches[$item['module']][$item['type']][] = $item['item'];
            }
            $vars = array('id', 'title', 'link', 'time');
            foreach ($batches as $m => $mData) {
                foreach ($mData as $t => $tData) {
                    $content[$m . '-' . $t] = Pi::service('module')->content(
                        $vars,
                        array(
                            'module'    => $m,
                            'type'      => $t,
                            'id'        => $tData
                        )
                    );
                }
            }

            $list = array();
            array_walk($items, function ($item) use ($modules, $content, &$list) {
                $key = $item['module'] . '-' . $item['type'];
                if (isset($content[$key]) && isset($modules[$item['module']])) {
                    $found = false;
                    foreach ($content[$key] as $data) {
                        if ($data['id'] == $item['item']) {
                            $item['url'] = $data['link'];
                            $item['title'] = $data['title'];
                            $item['time'] = $data['time'];
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        $item['module'] = $modules[$item['module']]['title'];
                        $list[] = $item;
                    }
                }
            });

            $paginator = Paginator::factory($count, array(
                'limit'       => $limit,
                'page'        => $page,
                'url_options' => array(
                    'route' => 'tag',
                    'params' => array(
                        'tag'    => $tag,
                        'm'      => $module
                    )
                ),
            ));
        }

        $this->view()->assign(array(
            'paginator'     => $paginator,
            'list'          => $list,
            'tag'           => $tag,
            'count'         => $count,
            'm'             => $module,
            'moduleTitle'   => $moduleTitle,
        ));

        $this->view()->setTemplate('list');
    }
}
