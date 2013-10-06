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
     * Comment portal
     */
    public function indexAction()
    {
        // Portal
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
            'module'   => array(
                'title' => __('Comment posts by module'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                )),
            ),
        );

        // Statistics
        $counts = array(
            'total'     => array(
                'title' => __('Total posts'),
                'count' => Pi::api('comment')->getCount(),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                )),
            ),
            'active'     => array(
                'title' => __('Active posts'),
                'count' => Pi::api('comment')->getCount(array('active' => 1)),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 1,
                )),
            ),
            'inactive'     => array(
                'title' => __('Inactive posts'),
                'count' => Pi::api('comment')->getCount(array('active' => 0)),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 0,
                )),
            ),
        );
        //d($counts);

        // Top users
        $rowset = Pi::model('post', 'comment')->count(
            array('active' => 1),
            array('group' => 'uid', 'limit' => 5)
        );
        $users = array();
        foreach ($rowset as $row) {
            $users[$row['uid']] = array(
                'count' => (int) $row['count'],
            );
        }
        if ($users) {
            $userNames = Pi::service('user')->get(array_keys($users), 'name');
            array_walk($users, function (&$user, $uid) use ($userNames) {
                $user['name'] = $userNames[$uid];
                $user['profile'] = Pi::service('user')->getUrl('profile', $uid);
                $user['url'] = Pi::api('comment')->getUrl(
                    'user',
                    array('uid' => $uid)
                );
            });
        }
        //d($users);

        // Top targets
        $rowset = Pi::model('post', 'comment')->count(
            array('active' => 1),
            array('group' => 'root', 'limit' => 5)
        );
        $roots = array();
        foreach ($rowset as $row) {
            $roots[$row['root']] = (int) $row['count'];
        }
        $rootIds = array_keys($roots);
        $targets = Pi::api('comment')->getTargetsByRoot($rootIds);
        //$targets = Pi::api('comment')->getTargetList(array('root' => $rootIds));
        array_walk($targets, function (&$target, $rootId) use ($roots) {
            $target['count'] = $roots[$rootId];
        });
        //d($targets);

        // Module stats
        $modulelist = Pi::registry('modulelist')->read('active');
        $where = array(
            'post.active'   => 1,
            'root.module'   => array_keys($modulelist),
        );
        $select = Pi::db()->select();
        $select->from(
            array('post' => Pi::model('post', 'comment')->getTable())
        );
        $select->columns(array('count' => Pi::db()->expression('COUNT(*)')));
        $select->join(
            array('root' => Pi::model('root', 'comment')->getTable()),
            'root.id=post.root',
            array('module', 'category')
        );
        $select->where($where);
        $select->group(array('root.module', 'root.category'));
        $resultSet = Pi::db()->query($select);
        $list = array();
        foreach ($resultSet as $set) {
            $list[$set['module']][$set['category']] = (int) $set['count'];
        }
        $categories = Pi::registry('category', 'comment')->read();
        $modules = array();
        foreach ($modulelist as $name => $mData) {
            if (!isset($list[$name])) {
                continue;
            }
            $data = array(
                'title' => $mData['title'],
                'count' => 0,
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                    'name'          => $name,
                )),
            );
            $mCount = 0;
            foreach ($categories[$name] as $category => $cData) {
                $categoryData = array(
                    'title' => $cData['title'],
                    'count' => 0,
                    'url'   => $this->url('', array(
                        'controller'    => 'list',
                        'action'        => 'module',
                        'name'          => $name,
                        'category'      => $category,
                    )),
                );
                if (isset($list[$name][$category])) {
                    $mCount += $list[$name][$category];
                    $categoryData['count'] = $list[$name][$category];
                }
                $data['categories'][$category] = $categoryData;
            }
            $data['count'] = $mCount;
            $modules[$name] = $data;
        }
        //d($modules);

        $this->view()->assign(array(
            'title'     => $title,
            'counts'    => $counts,
            'modules'   => $modules,
            'users'     => $users,
            'targets'   => $targets,
            'links'     => $links,
        ));

        $this->view()->setTemplate('comment-index');
    }
}
