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
        $active = _get('active');
        if (-1 == $active) {
            $active = null;
        }
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $posts = Pi::api('api', 'comment')->getList(
            array('active' => $active),
            $limit,
            $offset
        );
        /*
        // Comprehensive mode
        $posts = Pi::api('api', 'comment')->renderList($posts, array(
            'user'      => array(
                'field'     => 'name',
                'url'       => 'comment',
                'avatar'    => 'small'
            ),
            'target'    => true,
            'operation'     => array(
                'uid'       => Pi::service('user')->getId(),
                'section'   => 'admin',
                'level'     => 'admin',
            ),
        ));
        */
        /*
        // Lean mode
        $posts = Pi::api('api', 'comment')->renderList($posts, array(
            'user'      => true,
            'target'    => true,
            'operation' => true,
        ));
        */
        // Default mode
        $posts = Pi::api('api', 'comment')->renderList($posts, array(
            'operation'     => 'admin',
            'user'          => array(
                'avatar'    => 'medium',
            ),
        ));
        $count = Pi::service('comment')->getCount(array('active' => $active));

        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'    => array(
                    'active' => (null === $active) ? -1 : $active
                ),
            ),
        ));
        if (null === $active) {
            $title = _a('All comment posts');
        } elseif (!$active) {
            $title = _a('All inactive comment posts');
        } else {
            $title = _a('All active comment posts');
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'active'    => $active,
        ));

        $this->view()->setTemplate('comment-list');
    }

    /**
     * Active comment posts of a user
     */
    public function userAction()
    {
        $uid        = _get('uid');
        $keyword    = _get('keyword');
        if (!empty($keyword)) {
            $uid = $keyword;
        } else {
            $keyword = $uid;
        }
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
                'avatar'    => Pi::service('avatar')->get($uid, 'medium'),
            );
        } else {
            $this->view()->assign(array(
                'title' => _a('Comments by username or id'),
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
        $posts = Pi::api('api', 'comment')->getList(
            $where,
            $limit,
            $offset
        );
        $posts = Pi::api('api', 'comment')->renderList($posts, array(
            'user'      => false,
            'target'    => true,
            'operation' => 'admin',
        ));
        $count = Pi::service('comment')->getCount($where);

        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'        => array(
                    'uid'       => $uid,
                    'active'    => $active,
                ),
            ),
        ));
        $title = _a('Comment posts of user');
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'user'      => $user,
            'active'    => $active,
        ));
        
        // Get count
        $allCount = null === $active
            ? $count 
            : Pi::service('comment')->getCount(array_merge(
                $where,
                array('active' => null)
            ));
        $activeCount = 1 === $active
            ? $count
            : Pi::service('comment')->getCount(array_merge(
                $where,
                array('active' => 1)
            ));
        $inactiveCount = 0 === $active
            ? $count
            : Pi::service('comment')->getCount(array_merge(
                $where,
                array('active' => 0)
            ));

        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => _a('All Posts') . " ({$allCount})",
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'uid'       => $uid,
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => _a('Active Posts') . " ({$activeCount})",
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'uid'       => $uid,
                    'active'    => 1,
                ))
            ),
            array(
                'active'    => 0 === $active,
                'label'     => _a('Inactive posts') . " ({$inactiveCount})",
                'href'      => $this->url('', array(
                    'action'    => 'user',
                    'uid'       => $uid,
                    'active'    => 0,
                ))
            ),
        );
        $this->view()->assign(array(
            'tabs'      => $navTabs,
            'keyword'   => $keyword,
        ));
        $this->view()->setTemplate('comment-user');
    }

    /**
     * Active comment posts of a module, or with its type
     */
    public function moduleAction()
    {
        $module = _get('name');
        if (!$module) {
            $title = _a('Comment types');

            $modulelist = Pi::registry('modulelist')->read('active');
            $rowset = Pi::model('type', 'comment')->select(array(
                'module'    => array_keys($modulelist),
            ));
            $types = array();
            foreach ($rowset as $row) {
                $type = $row['name'];
                $types[$row['module']][$type] = array(
                    'title'     => $row['title'],
                    'url'       => $this->url('', array(
                        'controller'    => 'list',
                        'action'        => 'module',
                        'name'          => $row['module'],
                        'type'      => $type,
                    )),
                );
            }
            $modules = array();
            foreach ($modulelist as $name => $data) {
                if (!isset($types[$name])) {
                    continue;
                }
                $modules[$name] = array(
                    'title'         => $data['title'],
                    'url'           => $this->url('', array(
                        'controller'    => 'list',
                        'action'        => 'module',
                        'name'          => $name,
                    )),
                    'types'    => $types[$name],
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

        $type = _get('type') ?: '';
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
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
        $posts = Pi::api('api', 'comment')->renderList($posts, array(
            'operation' => 'admin',
            'user'      => array(
                'avatar'    => 'medium',
            ),
        ));
        $count = Pi::service('comment')->getCount($where);

        $params = array('name' => $module, 'active' => $active);
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
        if ($typeData) {
            $title = sprintf(
                _a('Comment posts of Module %s with type %s'),
                $moduleData['title'],
                $typeData['title']
            );
        } else {
            $title = sprintf(
                _a('Comment posts of Module %s'),
                $moduleData['title']
            );
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'posts'     => $posts,
            'paginator' => $paginator,
            'module'    => $moduleData,
            'type'  => $typeData,
            'active'    => $active,
        ));
        
        // Get count
        $allCount = null === $active
            ? $count 
            : Pi::service('comment')->getCount(array_merge(
                $where,
                array('active' => null)
            ));
        $activeCount = 1 === $active
            ? $count
            : Pi::service('comment')->getCount(array_merge(
                $where,
                array('active' => 1)
            ));
        $inactiveCount = 0 === $active
            ? $count
            : Pi::service('comment')->getCount(array_merge(
                $where,
                array('active' => 0)
            ));

        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => _a('All Posts') . " ({$allCount})",
                'href'      => $this->url('', array(
                    'action'    => 'module',
                    'name'      => $module,
                    'type'  => $type,
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => _a('Active Posts') . " ({$activeCount})",
                'href'      => $this->url('', array(
                    'action'    => 'module',
                    'name'      => $module,
                    'type'  => $type,
                    'active'    => 1,
                ))
            ),
            array(
                'active'    => 0 === $active,
                'label'     => _a('Inactive posts') . " ({$inactiveCount})",
                'href'      => $this->url('', array(
                    'action'    => 'module',
                    'name'      => $module,
                    'type'  => $type,
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
        $active = _get('active', 1);
        $active = $active ? 1 : null;
        $page   = _get('page', 'int') ?: 1;
        $limit  = $this->config('list_limit') ?: 10;
        $offset = ($page - 1) * $limit;

        $targets = Pi::api('api', 'comment')->getTargetList(
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
            'name'      => _a('Guest'),
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
            $roots = array_keys($targets);
            $model = $this->getModel('post');
            $select = $model->select()
                ->where(array('root' => $roots ?: array()))
                ->columns(array('root', 'count' => new Expression('count(*)')))
                ->group(array('root'));
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $targets[$row->root]['count'] = $row->count;
            }
        }

        $params = (null === $active) ? array() : array('active' => $active);
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'limit'         => $limit,
            'url_options'   => array(
                'params'    => $params,
            ),
        ));
        if (null === $active) {
            $title = _a('All commented articles');
        } else {
            $title = _a('All active commented articles');
        }
        $this->view()->assign('comment', array(
            'title'     => $title,
            'count'     => $count,
            'targets'   => $targets,
            'paginator' => $paginator,
        ));

        /*
        $navTabs = array(
            array(
                'active'    => null === $active,
                'label'     => _a('Articles with comments'),
                'href'      => $this->url('', array(
                    'action'    => 'article',
                ))
            ),
            array(
                'active'    => 1 == $active,
                'label'     => _a('Articles with active comments'),
                'href'      => $this->url('', array(
                    'action'    => 'article',
                    'active'    => 1,
                ))
            ),
        );
        $this->view()->assign(array(
            'tabs'      => $navTabs,
        ));
        */
        $this->view()->setTemplate('comment-article', '', 'front');
    }

    /**
     * Batch operation
     *
     * @internal string|array $from
     */
    public function batchAction()
    {
        $op         = _post('op');
        $uids       = _post('uids');
        $uid        = _post('uid');
        $all        = _post('all');
        $from       = _post('from', array());

        $model = Pi::model('post', 'comment');
        if ($uid && $all) {
            $where = array('uid' => $uid);
        } elseif ($uids) {
            $where = array('uid' => $uids);
        } else {
            $where = false;
        }
        if ($where) {
            switch ($op) {
                case 'enable':
                    $model->update(array('active' => 1), $where);
                    break;
                case 'disable':
                    $model->update(array('active' => 0), $where);
                    break;
                case 'delete':
                    $model->delete($where);
                    break;
                default:
                    break;
            }
        }
        $result = array(
            'status'    => 1,
            'message'   => _a('Operation succeeded.'),
        );
        $message = $result['message'];

        // List, module/type, user
        if (!$from) {
            $from = array('action' => 'index');
        } elseif (is_string($from)) {
            $from = array('action'  => $from);
        }
        if (empty($from['action'])) {
            $from['action'] = 'index';
        }
        $this->jump($from, $message);
    }
}
