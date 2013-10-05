<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;

class IndexController extends ActionController
{
    /**
     * Demo for article with comments
     */
    public function indexAction()
    {
        $title = sprintf(__('Comment portal for %s'), Pi::config('sitename'));
        $links = array(
            'build'   => array(
                'title' => __('Build comment data for demo articles'),
                'url'   => $this->url('comment', array(
                    'controller'    => 'demo',
                    'action'        => 'build',
                )),
            ),
            'article'   => array(
                'title' => __('Demo article with comments'),
                'url'   => $this->url('comment', array(
                    'controller'    => 'demo',
                )),
            ),
            'all'   => array(
                'title' => __('All comment posts'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                )),
            ),
            'all-active'   => array(
                'title' => __('All active comment posts'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 1,
                )),
            ),
            'all-inactive'   => array(
                'title' => __('All inactive comment posts'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 0,
                )),
            ),
            'user'   => array(
                'title' => __('Comment posts by user'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'user',
                )),
            ),
        );

        $modulelist = Pi::registry('modulelist')->read('active');
        $rowset = Pi::model('category', 'comment')->select(array(
            'module'    => array_keys($modulelist),
        ));
        $categories = array();
        foreach ($rowset as $row) {
            $categories[$row['module']][$row['category']] = array(
                'title'     => $row['title'],
                'active'    => (int) $row['active'],
                'url'       => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $row['module'],
                    'category'      => $row['category'],
                )),
                'enable'    => array(
                    'title' => $row['active'] ? __('Disable') : __('Enable'),
                    'url'   => $this->url('', array(
                        'controller'    => 'list',
                        'action'        => 'enable',
                        'category'      => $row['category'],
                        'flag'          => $row['active'] ? 0 : 1,
                    )),
                ),
            );
        }
        $modules = array();
        foreach ($modulelist as $name => $data) {
            if (!isset($categories[$name])) {
                continue;
            }
            $modules[$name] = array(
                'title'         => $data['title'],
                'url'           => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $name,
                )),
                'categories'    => $categories[$name],
            );
        }
        $this->view()->assign(array(
            'title'     => $title,
            'links'     => $links,
            'modules'   => $modules,
        ));

        $this->view()->setTemplate('comment-index');
    }
}
