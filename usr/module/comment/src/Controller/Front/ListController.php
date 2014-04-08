<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Zend\Db\Sql\Expression;

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
        /*
        $active = _get('active');
        //vd($active);
        if (null !== $active) {
            $active = (int) $active;
        }
        */
        $active = 1;
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $where = array('active' => $active);
        $posts = Pi::api('api', 'comment')->getList(
            $where,
            $limit,
            $offset
        );
        $renderOptions = array(
            'operation' => $this->config('display_operation'),
            'user'      => array(
                'avatar'    => 'medium',
            ),
        );
        $posts = Pi::api('api', 'comment')->renderList($posts, $renderOptions);
        $count = Pi::api('api', 'comment')->getCount($where);

        //$params = (null === $active) ? array() : array('active' => $active);
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            /*
            'url_options'   => array(
                'params'    => $params,
            ),
            */
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

        $this->view()->setTemplate('comment-list');
    }

    /**
     * List of comment posts of a root
     *
     * @return string
     */
    public function rootAction()
    {
        $rootId     = _get('root', 'int') ?: 1;
        $root       = Pi::model('root', 'comment')->find($rootId);
        $isActive   = null;
        $count      = null;
        $target     = null;
        $posts      = null;
        $paginator  = null;

        if ($root) {
            $isActive = $root['active'];
            $page   = _get('page', 'int') ?: 1;
            $limit  = $this->config('list_limit') ?: 10;
            $offset = ($page - 1) * $limit;
            $posts  = Pi::api('api', 'comment')->getList($rootId, $limit, $offset);
            $renderOptions = array(
                'operation' => $this->config('display_operation'),
                'user'      => array(
                    'avatar'    => 'medium',
                ),
            );
            $posts = Pi::api('api', 'comment')->renderList($posts, $renderOptions);
            $count = Pi::api('api', 'comment')->getCount($rootId);

            $target = Pi::api('api', 'comment')->getTarget($rootId);

            $paginator = Paginator::factory($count, array(
                'page'          => $page,
                'limit'         => $limit,
                'url_options'   => array(
                    'params'        => array(
                        'root'      => $root->id,
                    ),
                ),
            ));
        } else {

        }

        $title = __('Comment posts of article');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'root'      => $rootId,
            'active'    => $isActive,
            'target'    => $target,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
        ));

        $this->view()->setTemplate('comment-root');
    }

    /**
     * Active comment posts of a user
     */
    public function userAction()
    {
        $my = _get('my', 'int');
        if ($my) {
            Pi::service('authentication')->requireLogin();
            $uid = Pi::user()->getId();
            $active = _get('active');
            if (null !== $active) {
                $active = (int) $active;
            }
            $opOptions = array(
                'admin' => false,
            );
        } else {
            $uid        = _get('uid', 'int') ?: Pi::user()->getId();
            $active     = 1;
            $opOptions  = $this->config('display_operation');
        }
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $where = array('uid' => $uid, 'active' => $active);
        $posts = Pi::api('api', 'comment')->getList(
            $where,
            $limit,
            $offset
        );
        $renderOptions = array(
            'user'      => false,
            'operation' => $opOptions,
        );
        $posts = Pi::api('api', 'comment')->renderList($posts, $renderOptions);
        $count = Pi::api('api', 'comment')->getCount($where);

        if ($my) {
            $params = array('my' => 1);
        } else {
            $params = $where;
        }
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'    => $params,
            ),
        ));
        if ($my) {
            $title          = __('My comments');
            $user           = null;
            $template       = 'comment-my-post';

            $navTabs = array(
                array(
                    'active'    => null === $active,
                    'label'     => __('My comments'),
                    'href'      => $this->url('', array(
                        'action'    => 'user',
                        'my'        => 1,
                    ))
                ),
                array(
                    'active'    => 1 == $active,
                    'label'     => __('My active comments'),
                    'href'      => $this->url('', array(
                        'action'    => 'user',
                        'my'        => 1,
                        'active'    => 1,
                    ))
                ),
                array(
                    'active'    => 0 === $active,
                    'label'     => __('My pending comments'),
                    'href'      => $this->url('', array(
                        'action'    => 'user',
                        'my'        => 1,
                        'active'    => 0,
                    ))
                ),
            );
            $this->view()->assign(array(
                'tabs'      => $navTabs,
            ));

        } else {
            $user           = Pi::service('user')->get($uid, array('name'));
            $user['avatar'] = Pi::service('avatar')->get($uid, 'medium');
            $user['url']    = Pi::service('user')->getUrl('profile', $uid);
            $title          = __('Comment posts of user');
            $template       = 'comment-user';
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'user'      => $user,
        ));

        $this->view()->setTemplate($template);
    }

    /**
     * Active comment posts of a module, or with its type
     */
    public function moduleAction()
    {
        //$active = _get('active', 'int') ?: 1;
        $active = 1;
        $module = _get('name') ?: 'comment';
        $type = _get('type') ?: '';
        $page   = _get('page', 'int') ?: 1;
        $limit = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;
        $where = array('module' => $module, 'active' => $active);

        $moduleData = Pi::registry('module')->read($module);
        $moduleData = array(
            'name'  => $module,
            'title' => $moduleData['title'],
        );
        $typeData = array();
        if ($type) {
            $typeData = Pi::registry('type', 'comment')->read(
                $module,
                $type
            );
            $where['type'] = $type;
        }
        $posts = Pi::api('api', 'comment')->getList(
            $where,
            $limit,
            $offset
        );
        $renderOptions = array(
            'operation' => $this->config('display_operation'),
            'user'      => array(
                'avatar'    => 'medium',
            ),
        );
        $posts = Pi::api('api', 'comment')->renderList($posts, $renderOptions);
        $count = Pi::api('api', 'comment')->getCount($where);

        //$params = array('name' => $module, 'active' => $active);
        $params = array('name' => $module);
        if ($type) {
            $params['type'] = $type;
        }
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'        => $params,
            ),
        ));
        
        $navTabs = array(
            array(
                'active'    => empty($type),
                'label'     => __('All'),
                'href'      => $this->url('', array(
                    'name'      => $module,
                    'module'    => 'list',
                    'action'    => 'module',
                ))
            ),
        );
        $allType = Pi::registry('type', 'comment')->read();
        foreach ($allType[$module] as $name => $row) {
            $navTabs[] = array(
                'active'    => $type == $name,
                'label'     => $row['title'],
                'href'      => $this->url('', array(
                    'name'      => $module,
                    'type'      => $name,
                    'module'    => 'list',
                    'action'    => 'module',
                )),
            );
        }
        $this->view()->assign(array(
            'tabs'      => $navTabs,
        ));
            
        $title = __('Comment posts of module');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'module'    => $moduleData,
            'type'  => $typeData,
        ));

        $this->view()->setTemplate('comment-module');
    }

    /**
     * All commented articles
     */
    public function articleAction()
    {
        /*
        $active = _get('active');
        if (null !== $active) {
            $active = 1;
        }
        */
        $my = _get('my', 'int');
        if ($my) {
            Pi::service('authentication')->requireLogin();
            $uid = Pi::user()->getId();
            $active = _get('active');
            if (null !== $active) {
                $active = (int) $active;
            }
            $where = array('author' => $uid, 'active' => $active);
        } else {
            $active = 1;
            $where  = array('active' => $active);
        }

        //$active = 1;
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $targets = Pi::api('api', 'comment')->getTargetList(
            $where,
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
            $users = Pi::service('user')->mget($uids, array('name'));
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
            $data['comment_url'] = Pi::api('api', 'comment')->getUrl('root', array(
                'root'  => $root,
            ));
        });
        //d($targets);

        $count = Pi::api('api', 'comment')->getTargetCount(array(
            'active'    => $active,
        ));

        if ($targets) {
            $model = $this->getModel('post');
            $select = $model->select()
                ->where(array('root' => array_keys($targets)))
                ->columns(array('root', 'count' => new Expression('count(*)')))
                ->group(array('root'));
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $targets[$row->root]['count'] = $row->count;
            }
        }

        //$params = (null === $active) ? array() : array('active' => $active);
        if ($my) {
            if (null === $active) {
                $params = array('my' => 1);
            } else {
                $params = array('my' => 1, 'active' => $active);
            }
        } else {
            $params = array();
        }
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'    => $params,
            ),
        ));
        if (null === $active) {
            $title = __('All commented articles');
        } else {
            $title = __('Articles with comments');
        }

        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'targets'   => $targets,
            'paginator' => $paginator,
        ));

        if ($my) {
            $navTabs = array(
                array(
                    'active'    => $my && null === $active,
                    'label'     => __('My articles'),
                    'href'      => $this->url('', array(
                        'action'    => 'article',
                        'my'        => 1,
                    ))
                ),
                array(
                    'active'    => $my && $active,
                    'label'     => __('My articles with active comments'),
                    'href'      => $this->url('', array(
                        'action'    => 'article',
                        'my'        => 1,
                        'active'    => 1,
                    ))
                ),
            );
            $this->view()->assign(array(
                'tabs'      => $navTabs,
            ));
        }

        $this->view()->setTemplate('comment-article');
    }

    /**
     * All posts commented on me
     */
    public function receivedAction()
    {
        /*
        $active = _get('active');
        if (null !== $active) {
            $active = 1;
        }
        */
        $my     = _get('my', 'int');
        $uid    = _get('uid', 'int');
        $active = _get('active');
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        if (!$my && $uid) {
            $active = 1;

        } else  {
            Pi::service('authentication')->requireLogin();
            $my     = 1;
            $uid    = Pi::user()->getId();
            if (null !== $active) {
                $active = (int) $active;
            }
        }
        $where  = array(
            'author' => $uid,
            'active' => $active
        );

        $posts = Pi::api('api', 'comment')->getList(
            $where,
            $limit,
            $offset
        );
        $options = array(
            'user'  => array(
                'avatar'    => 'medium',
            ),
        );
        $posts = Pi::api('api', 'comment')->renderList($posts, $options);
        $count = Pi::api('api', 'comment')->getCount($where);

        if ($my) {
            $params = array(
                'my'        => 1,
                'active'    => $active,
            );
        } else {
            $params = array(
                'uid'       => $uid,
            );
        }
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'    => $params,
            ),
        ));
        if ($my) {
            $title          = __('Received comments');
            $user           = null;
            $template       = 'comment-my-received';

            $navTabs = array(
                array(
                    'active'    => null === $active,
                    'label'     => __('All Posts'),
                    'href'      => $this->url('', array(
                        'action'    => 'received',
                        'my'        => 1,
                    ))
                ),
                array(
                    'active'    => 1 == $active,
                    'label'     => __('Active Posts'),
                    'href'      => $this->url('', array(
                        'action'    => 'received',
                        'my'        => 1,
                        'active'    => 1,
                    ))
                ),
                array(
                    'active'    => 0 === $active,
                    'label'     => __('Inactive Posts'),
                    'href'      => $this->url('', array(
                        'action'    => 'received',
                        'my'        => 1,
                        'active'    => 0,
                    ))
                ),
            );
            $this->view()->assign(array(
                'tabs'      => $navTabs,
            ));

        } else {
            $user           = Pi::service('user')->get($uid, array('name'));
            $user['avatar'] = Pi::service('avatar')->get($uid, 'medium');
            $user['url']    = Pi::service('user')->getUrl('profile', $uid);
            $title          = __('Comment posts on user');
            $template       = 'comment-user-received';
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'user'      => $user,
        ));

        $this->view()->setTemplate($template);
    }
}
