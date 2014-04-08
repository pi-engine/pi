<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $title = sprintf(_a('Comment portal for %s'), Pi::config('sitename'));
        $links = array(
            'build'   => array(
                'title' => _a('Build comment data for demo articles'),
                'url'   => $this->url('', array(
                    'controller'    => 'index',
                    'action'        => 'build',
                )),
            ),
            'demo'   => array(
                'title' => _a('Demo article with comments'),
                'url'   => $this->url('comment', array(
                    'controller'    => 'demo',
                )),
            ),
            'all'   => array(
                'title' => _a('All comment posts'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                )),
            ),
            'all-active'   => array(
                'title' => _a('All active comment posts'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 1,
                )),
            ),
            'all-inactive'   => array(
                'title' => _a('All inactive comment posts'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 0,
                )),
            ),
            'article'   => array(
                'title' => _a('Commented articles'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'article',
                )),
            ),
            'user'   => array(
                'title' => _a('Comment posts by user'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'user',
                )),
            ),
            'module'   => array(
                'title' => _a('Comment posts by module'),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'module',
                )),
            ),
        );

        // Statistics
        $counts = array(
            'total'     => array(
                'title' => _a('Total posts'),
                'count' => Pi::api('api', 'comment')->getCount(),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                )),
            ),
            'active'     => array(
                'title' => _a('Active posts'),
                'count' => Pi::api('api', 'comment')->getCount(array('active' => 1)),
                'url'   => $this->url('', array(
                    'controller'    => 'list',
                    'action'        => 'index',
                    'active'        => 1,
                )),
            ),
            'inactive'     => array(
                'title' => _a('Inactive posts'),
                'count' => Pi::api('api', 'comment')->getCount(array('active' => 0)),
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
            $userNames = Pi::service('user')->mget(array_keys($users), 'name');
            array_walk($users, function (&$user, $uid) use ($userNames) {
                $user['name'] = $userNames[$uid];
                $user['profile'] = Pi::service('user')->getUrl('profile', $uid);
                $user['url'] = Pi::api('api', 'comment')->getUrl(
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
        $targets = Pi::api('api', 'comment')->getTargetsByRoot($rootIds);
        //$targets = Pi::api('api', 'comment')->getTargetList(array('root' => $rootIds));
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
            array('module', 'type')
        );
        $select->where($where);
        $select->group(array('root.module', 'root.type'));
        $resultSet = Pi::db()->query($select);
        $list = array();
        foreach ($resultSet as $set) {
            $list[$set['module']][$set['type']] = (int) $set['count'];
        }
        $types = Pi::registry('type', 'comment')->read();
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
            foreach ($types[$name] as $type => $cData) {
                $typeData = array(
                    'title' => $cData['title'],
                    'count' => 0,
                    'url'   => $this->url('', array(
                        'controller'    => 'list',
                        'action'        => 'module',
                        'name'          => $name,
                        'type'      => $type,
                    )),
                );
                if (isset($list[$name][$type])) {
                    $mCount += $list[$name][$type];
                    $typeData['count'] = $list[$name][$type];
                }
                $data['types'][$type] = $typeData;
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

    /**
     * Build demo comment post data
     */
    public function buildAction()
    {
        /*
        $roots = Pi::model('root', 'comment')->delete(array(
            'module'    => 'comment',
        ));
        foreach ($roots as $root) {
            Pi::api('api', 'comment')->delete($root->id);
        }
        */
        Pi::model('root', 'comment')->delete(array(
            'module'    => 'comment',
        ));
        Pi::model('post', 'comment')->delete(array(
            'module'    => 'comment',
        ));

        $rootIds = array();
        //$key = 1;
        for ($i = 1; $i <= 10; $i++) {
            $root = array(
                'module'    => 'comment',
                'item'      => $i,
                'type'  => 'article',
                'active'    => rand(0, 1),
            );
            $rootIds[] = Pi::api('api', 'comment')->addRoot($root);
        }

        for ($i = 1; $i <= 5; $i++) {
            $root = array(
                'module'    => 'comment',
                'item'      => $i,
                'type'  => 'custom',
                'active'    => rand(0, 1),
            );
            $rootIds[] = Pi::api('api', 'comment')->addRoot($root);
        }

        for ($i = 0; $i < 1000; $i++) {
            $post = array(
                'root'      => $rootIds[rand(0, 14)],
                'uid'       => rand(1, 5),
                'ip'        => Pi::service('user')->getIp(),
                'active'    => rand(0, 1),
                'content'   => sprintf(_a('Demo comment %d.'), $i + 1),
                'time'      => time() - rand(100, 100000),
            );
            Pi::api('api', 'comment')->addPost($post);
        }

        //exit();
        $this->redirect('comment', array(
            'action'    => 'index',
            'id'        => rand(1, 5),
            'enable'    => 'yes',
        ));
    }
}
