<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Controller\Front;

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
     * List of comment posts of a root
     *
     * @return string
     */
    public function indexAction()
    {
        $root   = _get('root', 'int') ?: 1;
        $page   = _get('page', 'int') ?: 1;
        //vd($page);
        $limit = Pi::config('comment_limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $posts = Pi::api('comment')->getList($root, $limit, $offset);
        $count = Pi::api('comment')->getCount($root);

        $target = Pi::api('comment')->getTarget($root);

        $users = array();
        $uids = array();
        $uids[] = $target['uid'];
        foreach ($posts as $post) {
            $uids[] = (int) $post['uid'];
        }
        if ($uids) {
            $uids = array_unique($uids);
            $users = Pi::service('user')->get($uids, array('name'));
            $avatars = Pi::service('avatar')->getList($uids, 'small');
            //vd($avatars);
            //vd($users);
            foreach ($users as $uid => &$data) {
                $data['url'] = Pi::service('user')->getUrl('profile', $uid);
                $data['avatar'] = $avatars[$uid];
            }
        }
        $users[0] = array(
            'avatar'    => Pi::service('avatar')->get(0, 'small'),
            'url'       => Pi::url('www'),
            'name'      => __('Guest'),
        );

        //vd($uids);
        //vd($users);
        $setUser = function ($uid) use ($users) {
            if (isset($users[$uid])) {
                return $users[$uid];
            } else {
                return $users[0];
            }
        };
        $target['user'] = $setUser($target['uid']);
        foreach ($posts as &$post) {
            $post['user'] = $setUser($post['uid']);
        }

        $paginator = Paginator::factory($count, array(
            'page'  => $page,
            'url_options'           => array(
                'params'        => array(
                    'root'      => $root,
                ),
            ),
        ));
        $title = sprintf(__('Comment posts of %s'), $target['title']);
        $this->view()->assign('comment', array(
            'title'     => $title,
            'root'      => $root,
            'target'    => $target,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
        ));

        $this->view()->setTemplate('comment-list');
    }

    /**
     * Active comment posts of a user
     */
    public function userAction()
    {
        $uid    = _get('uid', 'int') ?: Pi::user()->getIdentity();
        $page   = _get('page', 'int') ?: 1;
        $limit = Pi::config('comment_limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $posts = Pi::service('comment')->getList(
            array('uid' => $uid),
            $limit,
            $offset
        );
        $count = Pi::service('comment')->getCount(array('uid' => $uid));

        $targets = array();
        $rootIds = array();
        foreach ($posts as $post) {
            $rootIds[] = (int) $post['root'];
        }
        if ($rootIds) {
            $rootIds = array_unique($rootIds);
            $targets = Pi::api('comment')->getTargetList(array('root' => $rootIds));
        }
        foreach ($posts as &$post) {
            $post['target'] = $targets[$post['root']];
        }

        $user = Pi::service('user')->get($uid, array('name'));
        $user['avatar'] = Pi::service('avatar')->get($uid);
        $user['url'] = Pi::service('user')->getUrl('profile', $uid);

        $paginator = Paginator::factory($count, array(
            'page'  => $page,
            'url_options'           => array(
                'params'        => array(
                    'uid'      => $uid,
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

        $this->view()->setTemplate('comment-user');
    }

    /**
     * Active comment posts of a module, or with its category
     */
    public function moduleAction()
    {
        $module = _get('name') ?: 'comment';
        $category = _get('category') ?: '';
        $page   = _get('page', 'int') ?: 1;
        $limit = Pi::config('comment_limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $where = array('module' => $module);

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
        $posts = Pi::service('comment')->getList(
            $where,
            $limit,
            $offset
        );
        $count = Pi::service('comment')->getCount($where);

        $targets = array();
        $rootIds = array();
        foreach ($posts as $post) {
            $rootIds[] = (int) $post['root'];
        }
        if ($rootIds) {
            $rootIds = array_unique($rootIds);
            $targets = Pi::api('comment')->getTargetList(array(
                'root'  => $rootIds
            ));
        }
        foreach ($posts as &$post) {
            $post['target'] = $targets[$post['root']];
        }

        $users = array();
        $uids = array();
        foreach ($posts as $post) {
            $uids[] = (int) $post['uid'];
        }
        if ($uids) {
            $uids = array_unique($uids);
            $users = Pi::service('user')->get($uids, array('name'));
            $avatars = Pi::service('avatar')->getList($uids, 'small');
            foreach ($users as $uid => &$data) {
                $data['url'] = Pi::service('user')->getUrl('profile', $uid);
                $data['avatar'] = $avatars[$uid];
            }
        }
        $users[0] = array(
            'avatar'    => Pi::service('avatar')->get(0, 'small'),
            'url'       => Pi::url('www'),
            'name'      => __('Guest'),
        );

        //vd($uids);
        //vd($users);
        $setUser = function ($uid) use ($users) {
            if (isset($users[$uid])) {
                return $users[$uid];
            } else {
                return $users[0];
            }
        };
        foreach ($posts as &$post) {
            $post['user'] = $setUser($post['uid']);
        }

        $params = array('name' => $module);
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

        $this->view()->setTemplate('comment-module');
    }

    /**
     * All active comment posts
     */
    public function allAction()
    {
        $active = _get('active');
        //vd($active);
        if (null !== $active) {
            $active = (int) $active;
        }
        $page   = _get('page', 'int') ?: 1;
        $limit = Pi::config('comment_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $posts = Pi::service('comment')->getList(
            array('active' => $active),
            $limit,
            $offset
        );
        $count = Pi::service('comment')->getCount(array('active' => $active));

        $targets = array();
        $rootIds = array();
        foreach ($posts as $post) {
            $rootIds[] = (int) $post['root'];
        }
        if ($rootIds) {
            $rootIds = array_unique($rootIds);
            $targets = Pi::api('comment')->getTargetList(array(
                'root'  => $rootIds
            ));
        }
        foreach ($posts as &$post) {
            $post['target'] = $targets[$post['root']];
        }

        $users = array();
        $uids = array();
        foreach ($posts as $post) {
            $uids[] = (int) $post['uid'];
        }
        if ($uids) {
            $uids = array_unique($uids);
            $users = Pi::service('user')->get($uids, array('name'));
            $avatars = Pi::service('avatar')->getList($uids, 'small');
            foreach ($users as $uid => &$data) {
                $data['url'] = Pi::service('user')->getUrl('profile', $uid);
                $data['avatar'] = $avatars[$uid];
            }
        }
        $users[0] = array(
            'avatar'    => Pi::service('avatar')->get(0, 'small'),
            'url'       => Pi::url('www'),
            'name'      => __('Guest'),
        );

        //vd($uids);
        //vd($users);
        $setUser = function ($uid) use ($users) {
            if (isset($users[$uid])) {
                return $users[$uid];
            } else {
                return $users[0];
            }
        };
        foreach ($posts as &$post) {
            $post['user'] = $setUser($post['uid']);
        }

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

        $this->view()->setTemplate('comment-all');
    }
}
