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
use Pi\Paginator\Paginator;

/**
 * Comment list controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ListController extends ActionController
{
    /**
     * All comment posts
     */
    public function indexAction()
    {
        $active = _get('active');
        if (null !== $active) {
            $active = (int) $active;
        }
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $posts = Pi::api('comment')->getList(
            array('active' => $active),
            $limit,
            $offset
        );
        /*
        // Comprehensive mode
        $posts = Pi::api('comment')->renderList($posts, array(
            'user'      => array(
                'field'     => 'name',
                'url'       => 'comment',
                'avatar'    => 'small'
            ),
            'target'    => true,
            'operation'     => array(
                'uid'       => Pi::service('user')->getIdentity(),
                'section'   => 'admin',
                'level'     => 'admin',
            ),
        ));
        */
        /*
        // Lean mode
        $posts = Pi::api('comment')->renderList($posts, array(
            'user'      => true,
            'target'    => true,
            'operation' => true,
        ));
        */
        // Default mode
        $posts = Pi::api('comment')->renderList($posts, array(
            'operation'     => 'admin'
        ));
        $count = Pi::service('comment')->getCount(array('active' => $active));

        $params = (null === $active) ? array() : array('active' => $active);
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'url_options'   => array(
                'params'    => $params,
            ),
        ));
        if (null === $active) {
            $title = __('All comment posts');
        } elseif (!$active) {
            $title = __('All inactive comment posts');
        } else {
            $title = __('All active comment posts');
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
        ));

        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => __('All Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => __('Active Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                    'active'    => 1,
                ))
            ),
            array(
                'active'    => 0 === $active,
                'label'     => __('Inactive Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'index',
                    'active'    => 0,
                ))
            ),
        );
        $this->view()->assign(array(
            'tabs'      => $navTabs,
        ));
        $this->view()->setTemplate('comment-list');
    }

    /**
     * Active comment posts of a user
     */
    public function userAction()
    {
        $uid        = _get('uid');
        $userModel  = null;
        if (is_numeric($uid)) {
            $userModel = Pi::service('user')->getUser($uid);
        } elseif ($uid) {
            $userModel = Pi::service('user')->getUser($uid, 'identity');
        }
        if ($userModel && $uid = $userModel->get('id')) {
            $user = array(
                'name'      => $userModel->get('name'),
                'url'       => Pi::service('user')->getUrl('profile', $uid),
                'avatar'    => Pi::service('avatar')->get($uid),
            );
        } else {
            $this->view()->assign(array(
                'title' => __('Select a user'),
                'url'   => $this->url('', array('action' => 'user')),
            ));
            $this->view()->setTemplate('comment-user-select');

            return;
        }

        $active = _get('active');
        if (null !== $active) {
            $active = (int) $active;
        }

        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $where = array('uid' => $uid, 'active' => $active);
        $posts = Pi::api('comment')->getList(
            $where,
            $limit,
            $offset
        );
        $posts = Pi::api('comment')->renderList($posts, array(
            'user'      => false,
            'target'    => true,
            'operation' => 'admin',
        ));
        $count = Pi::service('comment')->getCount($where);

        $paginator = Paginator::factory($count, array(
            'page'  => $page,
            'url_options'           => array(
                'params'        => array(
                    'uid'       => $uid,
                    'active'    => $active,
                ),
            ),
        ));
        $title = sprintf(__('Comment posts of user %s'), $user['name']);
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'user'      => $user,
        ));

        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => __('All Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'uid'       => $uid,
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => __('Active Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'uid'       => $uid,
                    'active'    => 1,
                ))
            ),
            array(
                'active'    => 0 === $active,
                'label'     => __('Inactive Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'uid'       => $uid,
                    'active'    => 0,
                ))
            ),
        );
        $this->view()->assign(array(
            'tabs'      => $navTabs,
        ));
        $this->view()->setTemplate('comment-user');
    }

    /**
     * Active comment posts of a module, or with its category
     */
    public function moduleAction()
    {
        $module = _get('name');
        if (!$module) {
            $title = __('Comment categories');

            $modulelist = Pi::registry('modulelist')->read('active');
            $rowset = Pi::model('category', 'comment')->select(array(
                'module'    => array_keys($modulelist),
            ));
            $categories = array();
            foreach ($rowset as $row) {
                $category = $row['name'];
                $categories[$row['module']][$category] = array(
                    'title'     => $row['title'],
                    'url'       => $this->url('', array(
                        'controller'    => 'list',
                        'action'        => 'module',
                        'name'          => $row['module'],
                        'category'      => $category,
                    )),
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

            //d($modules);
            $this->view()->assign(array(
                'title'     => $title,
                'modules'   => $modules,
            ));

            $this->view()->setTemplate('comment-module-select');
            return;
        }

        $active = _get('active');
        if (null !== $active) {
            $active = (int) $active;
        }

        $category = _get('category') ?: '';
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $where = array('module' => $module, 'active' => $active);

        $moduleData = Pi::registry('module')->read($module);
        $moduleData = array(
            'name'  => $module,
            'title' => $moduleData['title'],
        );
        $categoryData = array();
        if ($category) {
            $categoryData = Pi::registry('category', 'comment')->read(
                $module,
                $category
            );
            $where['category'] = $category;
        }
        $posts = Pi::api('comment')->getList(
            $where,
            $limit,
            $offset
        );
        $posts = Pi::api('comment')->renderList($posts, array(
            'operation' => 'admin',
        ));
        $count = Pi::service('comment')->getCount($where);

        $params = array('name' => $module, 'active' => $active);
        if ($category) {
            $params['category'] = $category;
        }
        $paginator = Paginator::factory($count, array(
            'page'  => $page,
            'url_options'           => array(
                'params'        => $params,
            ),
        ));
        if ($categoryData) {
            $title = sprintf(
                __('Comment posts of Module %s with Category %s'),
                $moduleData['title'],
                $categoryData['title']
            );
        } else {
            $title = sprintf(
                __('Comment posts of Module %s'),
                $moduleData['title']
            );
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'module'    => $moduleData,
            'category'  => $categoryData,
        ));

        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => __('All Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'module',
                    'name'      => $module,
                    'category'  => $category,
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => __('Active Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'module',
                    'name'      => $module,
                    'category'  => $category,
                    'active'    => 1,
                ))
            ),
            array(
                'active'    => 0 === $active,
                'label'     => __('Inactive Posts'),
                'href'      => $this->url('', array(
                    'action'    => 'module',
                    'name'      => $module,
                    'category'  => $category,
                    'active'    => 0,
                ))
            ),
        );
        $this->view()->assign(array(
            'tabs'      => $navTabs,
        ));
        $this->view()->setTemplate('comment-module');
    }

    /**
     * All commented articles
     */
    public function articleAction()
    {
        $active = _get('active');
        if (null !== $active) {
            $active = 1;
        }
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $targets = Pi::api('comment')->getTargetList(
            array('active' => $active),
            $limit,
            $offset
        );

        $uids = array();
        foreach ($targets as $root => $target) {
            $uids[] = $target['uid'];
            $uids[] = $target['comment_uid'];
        }
        if ($uids) {
            $uids = array_unique($uids);
            $users = Pi::service('user')->get($uids, array('name'));
            $avatars = Pi::service('avatar')->getList($uids, 'small');
            array_walk($users, function (&$data, $uid) use ($avatars) {
                $data['url'] = Pi::service('user')->getUrl(
                    'profile',
                    $uid
                );
                $data['avatar'] = $avatars[$uid];
            });
        }
        $users[0] = array(
            'avatar'    => Pi::service('avatar')->get(0, 'small'),
            'url'       => Pi::url('www'),
            'name'      => __('Guest'),
        );
        array_walk($targets, function (&$data, $root) use ($users) {
            $data['user'] = isset($users[$data['uid']])
                ? $users[$data['uid']] : $users[0];
            $data['comment_user'] = isset($users[$data['comment_uid']])
                ? $users[$data['comment_uid']] : $users[0];
            $data['comment_url'] = Pi::api('comment')->getUrl('root', array(
                'root'  => $root,
            ));
        });
        //d($targets);

        $count = Pi::api('comment')->getTargetCount(array(
            'active'    => $active,
        ));

        $params = (null === $active) ? array() : array('active' => $active);
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'url_options'   => array(
                'params'    => $params,
            ),
        ));
        if (null === $active) {
            $title = __('All commented articles');
        } else {
            $title = __('All active commented articles');
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'targets'   => $targets,
            'paginator' => $paginator,
        ));

        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => __('Articles with comments'),
                'href'      => $this->url('', array(
                    'action'    => 'article',
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => __('Articles with active comments'),
                'href'      => $this->url('', array(
                    'action'    => 'article',
                    'active'    => 1,
                ))
            ),
        );
        $this->view()->assign(array(
            'tabs'      => $navTabs,
        ));
        $this->view()->setTemplate('comment-article', '', 'front');
    }
}
