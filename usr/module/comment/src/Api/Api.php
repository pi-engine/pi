<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\Db\Sql\Where;
use Pi\Db\RowGateway\RowGateway;
use Module\Comment\Form\PostForm;
use Zend\Mvc\Router\RouteMatch;

/**
 * Comment manipulation APIs
 *
 * - load($routeMatch)
 * - add($root, array $data)
 * - addRoot(array $data)
 * - get($id)
 * - getForm(array $data)
 * - getUrl($type, array $options)
 * - getRoot(array $condition|$id)
 * - getTarget($root)
 * - getList(array $condition|$root, $limit, $offset, $order)
 * - getTargetList(array $condition, $limit, $offset, $order)
 * - getCount(array $condition|$root)
 * - update($id, array $data)
 * - delete($id)
 * - approve($id, $flag)
 * - enable($root, $flag)
 * - deleteRoot($root, $flag)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Api extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'comment';

    /** @var string[] Post table columns */
    protected $postColumn = array(
        'id',
        'root',
        'reply',
        'uid',
        'ip',
        'time',
        'time_updated',
        'content',
        'markup',
        'active',
        'module'
    );

    /** @var string[] Comment root table columns */
    protected $rootColumn = array(
        'id',
        'module',
        'type',
        'item',
        'author',
        'active'
    );

    /**
     * Canonize comment post data
     *
     * @param $data
     *
     * @return array
     */
    protected function canonizePost($data)
    {
        $result = array();

        if (array_key_exists('active', $data)) {
            if (null === $data['active']) {
                unset($data['active']);
            } else {
                $data['active'] = (int) $data['active'];
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $this->postColumn)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Canonize comment root data
     *
     * @param $data
     *
     * @return array
     */
    protected function canonizeRoot($data)
    {
        $result = array();

        if (array_key_exists('active', $data)) {
            if (null === $data['active']) {
                unset($data['active']);
            } else {
                $data['active'] = (int) $data['active'];
            }
        }

        foreach ($data as $key => $value) {
            if (in_array($key, $this->rootColumn)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Get URLs
     *
     * For AJAX request, set `$options['return'] = 1;`
     *
     * @param string    $type
     * @param array     $options
     *
     * @return string
     */
    public function getUrl($type, array $options = array())
    {
        $params = array();
        switch ($type) {
            case 'post':
                if (!empty($options['post'])) {
                    $params = array(
                        'controller'    => 'post',
                        'id'            => (int) $options['post'],
                    );
                    unset($options['post']);
                }
                break;
            case 'approve':
                if (!empty($options['post'])) {
                    $params = array(
                        'controller'    => 'post',
                        'action'        => $type,
                        'id'            => (int) $options['post'],
                    );
                    if (isset($options['flag'])) {
                        $params['flag'] = $options['flag'];
                        unset($options['flag']);
                    }
                    unset($options['post']);
                }
                break;
            case 'delete':
            case 'edit':
            case 'reply':
                if (!empty($options['post'])) {
                    $params = array(
                        'controller'    => 'post',
                        'action'        => $type,
                        'id'            => (int) $options['post'],
                    );
                    unset($options['post']);
                }
                break;
            case 'submit':
                $params = array('controller' => 'post', 'action' => $type);
                break;
            case 'list':
                $params = array('controller' => 'list');
                break;
            case 'root':
                if (!empty($options['root'])) {
                    $rootId = $options['root'];
                    unset($options['root']);
                } elseif ($root = Pi::api('api', 'comment')->getRoot($options)) {
                    $rootId = $root['id'];
                } else {
                    $rootId = 0;
                }
                if ($rootId) {
                    $params = array(
                        'controller'    => 'list',
                        'root'          => (int) $rootId,
                    );
                }
                break;
            case 'user':
                $params = array(
                    'controller'    => 'list',
                    'action'        => 'user',
                );
                if (!empty($options['uid'])) {
                    $params['uid'] = (int) $options['uid'];
                    unset($options['uid']);
                }
                break;
            case 'module':
                if (!empty($options['name'])) {
                    $params = array(
                        'controller'    => 'list',
                        'action'        => 'module',
                        'name'          => $options['name'],
                    );
                    if (!empty($options['type'])) {
                        $params['type'] = $options['type'];
                        unset($options['type']);
                    }
                    unset($options['name']);
                }
                break;
            default:
                break;
        }
        if ($options) {
            $params = array_merge($options, $params);
        }
        /*
        // For AJAX calls
        if (isset($options['return'])) {
            $params['return'] = $options['return'];
        }
        */
        $url = Pi::service('url')->assemble('comment', $params);

        return $url;
    }

    /**
     * Load comment data for rendering against matched route
     *
     * Data array:
     * - root: [id, ]module, type, item, active
     * - count
     * - posts: id, uid, ip, content, time, active
     * - users: uid, name, avatar, url
     * - url_list
     * - url_submit
     * - url_ajax
     *
     * @param RouteMatch|array|string $routeMatch
     * @param array $options
     *
     * @return array|bool
     */
    public function load($routeMatch, array $options = array())
    {
        if ($routeMatch instanceof RouteMatch) {
            $params = $routeMatch->getParams();
        } else {
            $params = (array) $routeMatch;
        }
        $limit = Pi::config('leading_limit', 'comment') ?: 5;

        // Look up root against route data
        $root           = array();
        $module         = $params['module'];
        $controller     = $params['controller'];
        $action         = $params['action'];
        $typeList       = Pi::registry('type', 'comment')->read($module, '', null);
        if (isset($typeList['route'][$controller][$action])) {
            $lookupList = $typeList['route'][$controller][$action];
        } elseif (isset($typeList['locator'])) {
            $lookupList = $typeList['locator'];
        } else {
            return false;
        }

        $active = true;
        $lookup = function ($data) use ($params, &$active) {
            // Look up via locator callback
            if (!empty($data['locator'])) {
                $locator    = new $data['locator']($params['module']);
                $item       = $locator->locate($params);
                $active     = $data['active'];

                return $item;
            }

            // Look up via route
            if (!isset($params[$data['identifier']])) {
                return false;
            }
            $item = $params[$data['identifier']];
            if ($data['params']) {
                foreach ($data['params'] as $param => $value) {
                    if (!isset($params[$param]) || $value != $params[$param]) {
                        return false;
                    }
                }
            }
            $active = $data['active'];

            return $item;
        };

        // Look up against controller-action
        foreach ($lookupList as $key => $data) {
            $item = $lookup($data, $active);
            if ($item) {
                $root = array(
                    'module'    => $module,
                    'type'      => $key,
                    'item'      => $item,
                );
                break;
            }
        }

        if (!$root) {
            return false;
        }

        // Load translations
        Pi::service('i18n')->load('module/comment:default');

        $rootData = $this->getRoot($root);
        if (!$active) {
            $root['active'] = 0;
            $rootData['active'] = 0;
        }
        // Check against cache
        if ($rootData['id']) {
            $result = Pi::service('comment')->loadCache($rootData['id']);
            if ($result) {
                if (Pi::service()->hasService('log')) {
                    Pi::service('log')->info(
                        sprintf('Comment root %d is cached.', $rootData['id'])
                    );
                }
                if (!$active) {
                    $result['root']['active'] = 0;
                }
                return $result;
            }
        }

        $result = array(
            'root'          => $rootData ?: $root,
            'count'         => 0,
            'posts'         => array(),
            'users'         => array(),
            'url_list'      => '',
            'url_submit'    => $this->getUrl('submit'),
        );

        if ($rootData) {
            $result['count'] = $this->getCount($rootData['id']);

            //vd($result['count']);
            if ($result['count']) {
                $posts = $this->getList($rootData['id'], $limit);
                $opOption = isset($options['display_operation'])
                    ? $options['display_operation']
                    : Pi::service('config')->module('display_operation', 'comment');
                $avatarOption = isset($options['avatar'])
                    ? $options['avatar']
                    : 'medium';
                $renderOptions = array(
                    'target'    => false,
                    'operation' => $opOption,
                    'user'      => array(
                        'avatar'    => $avatarOption,
                    ),
                );
                $posts = $this->renderList($posts, $renderOptions);
                $result['posts'] = $posts;
                $result['url_list'] = $this->getUrl(
                    'root',
                    array('root'  => $rootData['id'])
                );

                $status = Pi::service('comment')->saveCache(
                    $rootData['id'],
                    $result
                );
                if ($status && Pi::service()->hasService('log')) {
                    Pi::service('log')->info(sprintf(
                        'Comment root %d is saved to cache.',
                        $rootData['id']
                    ));
                }
            }
        }
        
        return $result;
    }

    /**
     * Render post content
     *
     * @param array|RowGateway|string $post
     *
     * @return string
     */
    public function renderPost($post)
    {
        $content = '';
        $markup = 'text';
        if ($post instanceof RowGateway || is_array($post)) {
            $content = $post['content'];
            $markup = $post['markup'];
        } elseif (is_string($post)) {
            $content = $post;
        }
        $renderer = ('markdown' == $markup || 'html' == $markup)
            ? 'html' : 'text';
        $parser = ('markdown' == $markup) ? 'markdown' : false;
        $result = Pi::service('markup')->render($content, $renderer, $parser);

        return $result;
    }

    /**
     * Render list of posts
     *
     * Options:
     *  - user:
     *      - field: 'name'
     *      - avatar: false|<size>
     *      - url: 'profile'|'comment'
     *
     *  - operation: with array
     *      - uid: int
     *      - user: object
     *      - section: 'front'|'admin'
     *      - list: array(<name> => <title>)
     *      - level: member, author, admin; default as author
     *  - operation: with string for level
     *
     *  - target
     *
     * - Comprehensive mode
     * ```
     *  $posts = Pi::api('api', 'comment')->renderList($posts, array(
     *      'user'      => array(
     *          'field'     => 'name',
     *          'url'       => 'comment',
     *          'avatar'    => 'small',
     *      ),
     *      'target'    => true,
     *      'operation'     => array(
     *          'uid'       => Pi::service('user')->getId(),
     *          'section'   => 'admin',
     *          'level'     => 'author',
     *      ),
     *  ));
     * ```
     *
     * - Lean mode
     * ```
     *  $posts = Pi::api('api', 'comment')->renderList($posts, array(
     *      'user'      => true,
     *      'target'    => true,
     *      'operation' => true,
     *  ));
     * ```
     *
     * - Default mode
     * ```
     *  $posts = Pi::api('api', 'comment')->renderList($posts);
     * ```
     *
     * @param array $posts
     * @param array $options
     *
     * @return array
     */
    public function renderList(array $posts, array $options = array())
    {
        if (!$posts) {
            return $posts;
        }

        $ops = array();
        // Build authors
        if (!isset($options['user']) || $options['user']) {
            $op = isset($options['user'])
                ? (array) $options['user'] : array();
            $label = !empty($op['field']) ? $op['field'] : 'name';
            $avatar = isset($op['avatar']) ? $op['avatar'] : 'small';
            $url = isset($op['url']) ? $op['url'] : 'profile';

            $uids = array();
            foreach ($posts as $post) {
                $uids[] = $post['uid'];
            }
            if ($uids) {
                $uids = array_unique($uids);
                $users = Pi::service('user')->mget($uids, array($label));
                $avatars = null;
                if (false !== $avatar) {
                    $avatars = Pi::service('avatar')->getList($uids, $avatar);
                }
                array_walk(
                    $users,
                    function (&$data, $uid) use ($url, $avatars) {
                        if ('comment' == $url) {
                            $data['url'] = $this->getUrl(
                                'user',
                                array('uid' => $uid)
                            );
                        } else {
                            $data['url'] = Pi::service('user')->getUrl(
                                'profile',
                                $uid
                            );
                        }
                        if (null !== $avatars) {
                            $data['avatar'] = $avatars[$uid];
                        }
                    }
                );
            }
            $users[0] = array(
                'avatar'    => Pi::service('avatar')->get(0, $avatar),
                'url'       => Pi::url('www'),
                'name'      => __('Guest'),
            );

            $ops['users'] = $users;
        }

        // Build operations
        if (!isset($options['operation']) || $options['operation']) {
            if (!isset($options['operation'])) {
                $op = array();
            } elseif (is_string($options['operation'])) {
                $op = array('level' => $options['operation']);
            } else {
                $op = (array) $options['operation'];
            }

            $uid = $user = $list = $section = $admin = null;
            if (isset($op['uid'])) {
                $uid = (int) $op['uid'];
            }
            if (isset($op['user'])) {
                $user = $op['user'];
            }
            if (null === $uid) {
                if (null === $user) {
                    $uid = Pi::service('user')->getId();
                } else {
                    $uid = $user->get('id');
                }
            }

            if (isset($op['section'])) {
                $section = $op['section'];
            } else {
                $section = Pi::engine()->section();
            }

            if (isset($op['list'])) {
                $list = (array) $op['list'];
            }
            if (null === $list) {
                $list = array(
                    'edit'      => __('Edit'),
                    'approve'   => __('Enable'),
                    'delete'    => __('Delete'),
                    'reply'     => __('Reply'),
                );
            }

            $level      = isset($op['level']) ? $op['level'] : 'author';
            $isAdmin    = Pi::service('permission')->isAdmin('comment', $uid);
            $setOperations = function ($post) use (
                $list,
                $uid,
                $isAdmin,
                $level,
                $section
            ) {
                if ('admin' == $level && $isAdmin) {
                    $opList = array('edit', 'approve', 'delete', 'reply');
                } elseif ('author' == $level && $uid == $post['uid']) {
                    $opList = array('edit', 'delete', 'reply');
                } elseif ($uid) {
                    $opList = array('reply');
                } else {
                    $opList = array();
                }
                $operations = array();
                foreach ($opList as $op) {
                    if (!isset($list[$op])) {
                        continue;
                    }
                    $title = $url = '';
                    switch ($op) {
                        case 'edit':
                        case 'delete':
                            if ('admin' == $section) {
                                $url = Pi::service('url')->assemble(
                                    'admin',
                                    array(
                                        'module'        => 'comment',
                                        'controller'    => 'post',
                                        'action'        => $op,
                                        'id'            => $post['id'],
                                    )
                                );
                            } else {
                                $url = $this->getUrl($op, array(
                                    'post' => $post['id']
                                ));
                            }
                            $title = $list[$op];
                            break;
                        case 'approve':
                            if ($post['active']) {
                                $flag = 0;
                                $title = __('Disable');
                            } else {
                                $flag = 1;
                                $title = __('Enable');
                            }
                            if ('admin' == $section) {
                                $url = Pi::service('url')->assemble(
                                    'admin',
                                    array(
                                        'module'        => 'comment',
                                        'controller'    => 'post',
                                        'action'        => $op,
                                        'id'            => $post['id'],
                                        'flag'          => $flag,
                                    )
                                );
                            } else {
                                $url = $this->getUrl($op, array(
                                    'post'  => $post['id'],
                                    'flag'  => $flag,
                                ));
                            }
                            break;
                        case 'reply':
                            if ('admin' == $section) {
                            } else {
                                $url = $this->getUrl($op, array(
                                    'post' => $post['id']
                                ));
                            }
                            $title = $list[$op];
                            break;
                        default:
                            break;
                    }
                    if (!$url || !$title) {
                        continue;
                    }

                    $operations[$op] = array(
                        'title' => $title,
                        'url'   => $url,
                    );
                }

                return $operations;
            };

            $ops['operations'] = array(
                'uid'       => $uid,
                'is_admin'  => $isAdmin,
                'section'   => $section,
                'list'      => $list,
                'callback'  => $setOperations,
            );
        }

        // Build targets
        if (!isset($options['target']) || $options['target']) {
            $targets = array();
            $rootIds = array();
            foreach ($posts as $post) {
                $rootIds[] = (int) $post['root'];
            }
            if ($rootIds) {
                $rootIds = array_unique($rootIds);
                $targets = $this->getTargetList(array(
                    'root'  => $rootIds
                ));
            }
            $ops['targets'] = $targets;
        }

        array_walk($posts, function (&$post) use ($ops) {
            $post['content'] = $this->renderPost($post);
            $post['url'] = $this->getUrl('post', array(
                'post'  => $post['id']
            ));
            if (!empty($ops['users'])) {
                $uid = (int) $post['uid'];
                if (isset($ops['users'][$uid])) {
                    $post['user'] = $ops['users'][$uid];
                } else {
                    $post['user'] = $ops['users'][0];
                }
            }
            if (!empty($ops['targets'])) {
                $root = (int) $post['root'];
                if (isset($ops['targets'][$root])) {
                    $post['target'] = $ops['targets'][$root];
                } else {
                    $post['target'] = $ops['targets'][0];
                }
            }
            if (!empty($ops['operations'])
                && is_callable($ops['operations']['callback'])
            ) {
                $post['operations'] = $ops['operations']['callback']($post);
            }
        });

        return $posts;
    }

    /**
     * Get comment post edit form
     *
     * @param array $data
     *
     * @return PostForm
     */
    public function getForm(array $data = array())
    {
        $form = new PostForm;
        if ($data) {
            $form->setData($data);
        }

        return $form;
    }

    /**
     * Add comment of an item
     *
     * @param array $data root, uid, content, module, item, type, time
     *
     * @return int|bool
     */
    public function addPost(array $data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $postData = $this->canonizePost($data);
        if (!$id) {
            // Add root if not exist
            if (empty($postData['root'])) {
                $rootId = $this->addRoot($data);
                if (!$rootId) {
                    return false;
                }
                $postData['root'] = $rootId;

            // Verify root
            } else {
                $root = Pi::model('root', 'comment')->find($postData['root']);
                if (!$root) {
                    return false;
                }
                $type = Pi::registry('type', 'comment')->read(
                    $root['module'],
                    $root['type']
                );
                // Exit if type is disabled or not exist
                if (!$type) {
                    return false;
                }
                if (empty($postData['module'])) {
                    $postData['module'] = $root['module'];
                }
            }

            if (!isset($postData['time'])) {
                $postData['time'] = time();
            }
            if (isset($postData['time_updated'])) {
                unset($postData['time_updated']);
            }
            $row = Pi::model('post', 'comment')->createRow($postData);
        } else {
            $row = Pi::model('post', 'comment')->find($id);
            if (!isset($postData['time_updated'])) {
                $postData['time_updated'] = time();
            }
            foreach (array('module', 'reply', 'root', 'time', 'uid') as $key) {
                if (isset($postData[$key])) {
                    unset($postData[$key]);
                }
            }
            $row->assign($postData);
        }

        try {
            $row->save();
            $id = (int) $row->id;
        } catch (\Exception $d) {
            $id = false;
        }

        return $id;
    }

    /**
     * Add comment root of an item
     *
     * @param array $data module, item, author, type, time
     *
     * @return int|bool
     */
    public function addRoot(array $data)
    {
        $id = isset($data['id']) ? (int) $data['id'] : 0;
        if (isset($data['id'])) {
            unset($data['id']);
        }
        $type = Pi::registry('type', 'comment')->read(
            $data['module'],
            $data['type']
        );
        // Exit if type is disabled or not exist
        if (!$type) {
            return false;
        }

        if (!isset($data['author'])) {
            $callback = $type['callback'];
            $handler = new $callback($data['module']);
            $source = $handler->get($data['item']);
            $data['author'] = $source['uid'];
        }
        $rootData = $this->canonizeRoot($data);
        if (!$id) {
            $row = Pi::model('root', 'comment')->createRow($rootData);
        } else {
            $row = Pi::model('root', 'comment')->find($id);
            $row->assign($rootData);
        }

        try {
            $row->save();
            $id = (int) $row->id;
        } catch (\Exception $d) {
            $id = false;
        }

        return $id;
    }

    /**
     * Get a comment
     *
     * @param int $id
     *
     * @return array|bool   uid, content, time, active, IP
     */
    public function getPost($id)
    {
        $row = Pi::model('post', 'comment')->find($id);
        $result = $row ? $row->toArray() : false;

        return $result;
    }

    /**
     * Get root
     *
     * @param int|array $condition
     *
     * @return array    Module, type, item, callback, active
     */
    public function getRoot($condition)
    {
        if (is_scalar($condition)) {
            $row = Pi::model('root', 'comment')->find($condition);
            $result = $row ? $row->toArray() : array();
        } else {
            //if (!$condition) b();
            //vd($condition);
            $where = $this->canonizeRoot($condition);
            //vd($where);
            $rowset = Pi::model('root', 'comment')->select($where);
            if (count($rowset) == 1) {
                $result = $rowset->current()->toArray();
            } else {
                $result = array();
            }
        }

        return $result;
    }

    /**
     * Get target(s) content
     *
     * @param int|int[] $item
     * @param array $options Callback or module + type
     *
     * @return mixed
     */
    public function getTargetContent($item, array $options)
    {
        if (empty($options['module'])) {
            return array();
        }

        $items = (array) $item;
        if (!empty($options['callback'])) {
            $handler = new $options['callback']($options['module']);
            $list = array_values($handler->get($items));
        } else {
            $vars = array('title', 'url', 'uid', 'time');
            $conditions = array(
                'module'    => $options['module'],
                'type'      => empty($options['type']) ? '' : $options['type'],
                'id'        => $items,
            );
            $list = Pi::service('module')->content($vars, $conditions);
        }
        if (is_scalar($item)) {
            $result = current($list);
        } else {
            $result = $list;
        }

        return $result;
    }

    /**
     * Get target content of a root
     *
     * @param int $root
     *
     * @return array|bool    Title, url, uid, time
     */
    public function getTarget($root)
    {
        $rootData = $this->getRoot($root);
        if (!$rootData) {
            return false;
        }
        $target = Pi::model('type', 'comment')->select(array(
            'module'    => $rootData['module'],
            'name'      => $rootData['type'],
        ))->current();
        if (!$target) {
            return false;
        }
        $data = array(
            'module'    => $rootData['module'],
            'type'      => $rootData['type'],
            'callback'  => $target['callback'],
        );
        $result = $this->getTargetContent($rootData['item'], $data);
        /*
        if (!empty($target['callback'])) {
            $handler = new $target['callback']($rootData['module']);
            $result = $handler->get($rootData['item']);
        } else {
            $vars = array('title', 'url', 'uid', 'time');
            $conditions = array(
                'module'    => $rootData['module'],
                'type'      => $rootData['type'],
                'id'        => $rootData['item']
            );
            $list = Pi::service('module')->content($vars, $conditions);
            if ($list) {
                $result = current($list);
            } else {
                $result = false;
            }
        }
        */

        return $result;
    }

    /**
     * Get target list by root IDs
     *
     * @param array $ids
     *
     * @return array
     */
    public function getTargetsByRoot(array $ids)
    {
        $result = array();
        if (!$ids) {
            return $result;
        }

        $rowset = Pi::model('root', 'comment')->select(array('id' => $ids));
        //$roots = array();
        $items = array();
        foreach ($rowset as $row) {
            $id = (int) $row['id'];
            //$roots[$id] = $row->toArray();
            $items[$row['module']][$row['type']][$row['item']] = $id;
        }
        //d($items);
        $types = Pi::registry('type', 'comment')->read();
        $list = array();
        foreach ($items as $module => $mList) {
            foreach ($mList as $type => $cList) {
                if (!isset($types[$module][$type])) {
                    continue;
                }
                /*
                $callback = $types[$module][$type]['callback'];
                $handler = new $callback($module);
                $targets = $handler->get(array_keys($cList));
                foreach ($targets as $item => $target) {
                    $root = $cList[$item];
                    $list[$root] = $target;
                }
                */
                $data = array(
                    'module'    => $module,
                    'type'      => $type,
                    'callback'  => $types[$module][$type]['callback'],
                );
                $targets = $this->getTargetContent(array_keys($cList), $data);
                foreach ($targets as $target) {
                    $root = $cList[$target['id']];
                    $list[$root] = $target;
                }
            }
        }
        foreach ($ids as $root) {
            $result[$root] = $list[$root];
        }

        return $result;
    }

    /**
     * Get multiple targets being commented
     *
     * @param array|Where $condition
     * @param int|null    $limit
     * @param int         $offset
     * @param string|null $order
     *
     * @return array List of targets indexed by root id
     */
    public function getTargetList(
        $condition,
        $limit          = null,
        $offset         = 0,
        $order          = null
    ) {
        $result = array();

        if ($condition instanceof Where) {
            $where = $condition;
        } else {
            $whereRoot = array();
            $wherePost = $this->canonizePost($condition);
            /**/
            if (isset($wherePost['active'])) {
                $whereRoot['active'] = $wherePost['active'];
            }
            /**/
            if (isset($condition['type'])) {
                $whereRoot['type'] = $condition['type'];
            }

            $where = array();
            foreach ($wherePost as $field => $value) {
                $where['post.' . $field] = $value;
            }
            foreach ($whereRoot as $field => $value) {
                $where['root.' . $field] = $value;
            }
        }

        $select = Pi::db()->select();
        $select->from(
            array('root' => Pi::model('root', 'comment')->getTable()),
            array('id', 'module', 'type', 'item', 'author')
        );

        $select->join(
            array('post' => Pi::model('post', 'comment')->getTable()),
            'post.root=root.id',
            //array()
            array('time', 'uid')
        );
        $select->group('post.root');
        $select->where($where);
        $limit = (null === $limit)
            ? Pi::config('list_limit', 'comment')
            : (int) $limit;
        $order = (null === $order) ? 'post.time desc' : $order;
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }

        $types      = Pi::registry('type', 'comment')->read();
        $items      = array();
        $keyList    = array();
        $rowset     = Pi::db()->query($select);
        foreach ($rowset as $row) {
            $root = (int) $row['id'];
            $keyList[] = $root;
            $items[$row['module']][$row['type']][$row['item']] = array(
                'root'          => $root,
                'comment_time'  => (int) $row['time'],
                'comment_uid'   => (int) $row['uid'],
            );
        }

        $targetList = array();
        foreach ($items as $module => $mList) {
            foreach ($mList as $type => $cList) {
                if (!isset($types[$module][$type])) {
                    continue;
                }
                /*
                $callback = $targets[$module][$type]['callback'];
                $handler = new $callback($module);
                $targets = $handler->get(array_keys($cList));
                foreach ($targets as $item => $target) {
                    $root = $cList[$item]['root'];
                    $targetList[$root] = array_merge($target, $cList[$item]);
                }
                */

                $data = array(
                    'module'    => $module,
                    'type'      => $type,
                    'callback'  => $types[$module][$type]['callback'],
                );
                $targets = $this->getTargetContent(array_keys($cList), $data);
                foreach ($targets as $target) {
                    $item = $target['id'];
                    $root = $cList[$item]['root'];
                    $targetList[$root] = array_merge($target, $cList[$item]);
                }
            }
        }
        foreach ($keyList as $key) {
            $result[$key] = &$targetList[$key];
        }

        return $result;
    }

    /**
     * Get multiple comments
     *
     * @param int|array|Where   $condition Root id or conditions
     * @param int               $limit
     * @param int               $offset
     * @param string            $order
     *
     * @return array|bool
     */
    public function getList($condition, $limit = null, $offset = 0, $order = null)
    {
        $result = array();

        $isJoin = false;
        if ($condition instanceof Where) {
            $where  = $condition;
            $isJoin = true;
        } else {
            $whereRoot = array();
            if (is_array($condition)) {
                $wherePost = $this->canonizePost($condition);
                if (isset($condition['type'])) {
                    $whereRoot['type'] = $condition['type'];
                }
                if (isset($condition['author'])) {
                    $whereRoot['author'] = $condition['author'];
                }
                if ($whereRoot) {
                    $isJoin = true;
                }
            } else {
                $wherePost = array(
                    'root'      => (int) $condition,
                    'active'    => 1,
                );
            }
            //vd($wherePost);
            if ($isJoin) {
                $where = array();
                foreach ($wherePost as $field => $value) {
                    $where['post.' . $field] = $value;
                }
                foreach ($whereRoot as $field => $value) {
                    $where['root.' . $field] = $value;
                }
            } else {
                $where = $wherePost;
            }
        }

        if (!$isJoin) {
            $order = null === $order ? 'time desc' : $order;
            $select = Pi::model('post', 'comment')->select();
        } else {
            $order = null === $order ? 'post.time desc' : $order;
            $select = Pi::db()->select();
            $select->from(
                array('post' => Pi::model('post', 'comment')->getTable())
            );
            $select->join(
                array('root' => Pi::model('root', 'comment')->getTable()),
                'root.id=post.root',
                array()
            );
        }

        $select->where($where);
        $limit = (null === $limit)
            ? Pi::config('list_limit', 'comment')
            : (int) $limit;
        if ($limit) {
            $select->limit($limit);
        }
        if ($order) {
            $select->order($order);
        }
        if ($offset) {
            $select->offset($offset);
        }
        //$select->order($order);
        if (!$isJoin) {
            $rowset = Pi::model('post', 'comment')->selectWith($select);
            foreach ($rowset as $row) {
                $result[] = $row->toArray();
            }
        } else {
            $rowset = Pi::db()->query($select);
            foreach ($rowset as $row) {
                $result[] = (array) $row;
            }
        }

        return $result;
    }

    /**
     * Get comment count
     *
     * @param int|array     $condition Root id or conditions
     *
     * @return int|bool
     */
    public function getCount($condition = array())
    {
        $isJoin = false;
        if ($condition instanceof Where) {
            $where = $condition;
            $isJoin = true;
        } else {
            $whereRoot = array();
            //$wherePost = array();
            if (is_array($condition)) {
                $wherePost = $this->canonizePost($condition);
                if (isset($condition['type'])) {
                    $whereRoot['type'] = $condition['type'];
                }
                if (isset($condition['author'])) {
                    $whereRoot['author'] = $condition['author'];
                }
                if ($whereRoot) {
                    $isJoin = true;
                }
            } else {
                $wherePost = array(
                    'root'      => (int) $condition,
                    'active'    => 1,
                );
            }
            if ($isJoin) {
                $where = array();
                foreach ($wherePost as $field => $value) {
                    $where['post.' . $field] = $value;
                }
                foreach ($whereRoot as $field => $value) {
                    $where['root.' . $field] = $value;
                }
            } else {
                $where = $wherePost;
            }
        }

        if (!$isJoin) {
            $count = Pi::model('post', 'comment')->count($where);
        } else {
            $select = Pi::db()->select();
            $select->from(
                array('post' => Pi::model('post', 'comment')->getTable())
            );
            $select->columns(array('count' => Pi::db()->expression('COUNT(*)')));
            $select->join(
                array('root' => Pi::model('root', 'comment')->getTable()),
                'root.id=post.root',
                array()
            );
            $select->where($where);
            $row = Pi::db()->query($select)->current();
            $count = (int) $row['count'];
        }

        return $count;
    }

    /**
     * Get target count
     *
     * @param array|Where $condition
     *
     * @return int
     */
    public function getTargetCount($condition = array())
    {
        if ($condition instanceof Where) {
            $where = $condition;
        } else {
            $whereRoot = array();
            $wherePost = $this->canonizePost($condition);
            if (isset($wherePost['active'])) {
                $whereRoot['active'] = $wherePost['active'];
            }
            if (isset($condition['type'])) {
                $whereRoot['type'] = $condition['type'];
            }

            $where = array();
            foreach ($wherePost as $field => $value) {
                $where['post.' . $field] = $value;
            }
            foreach ($whereRoot as $field => $value) {
                $where['root.' . $field] = $value;
            }
        }

        $select = Pi::db()->select();
        $select->from(
            array('root' => Pi::model('root', 'comment')->getTable())
        );
        $select->columns(array(
            'count' => Pi::db()->expression('COUNT(DISTINCT root.id)')
        ));
        $select->join(
            array('post' => Pi::model('post', 'comment')->getTable()),
            'post.root=root.id',
            array()
        );
        //$select->group('post.root');
        $select->where($where);
        $row = Pi::db()->query($select)->current();
        $count = (int) $row['count'];

        return $count;
    }

    /**
     * Delete a comment
     *
     * @param int   $id
     *
     * @return bool
     */
    public function deletePost($id)
    {
        $row = Pi::model('post', 'comment')->find($id);
        if (!$row) {
            return false;
        }
        $result = true;
        try {
            $row->delete();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Approve/Disapprove a comment
     *
     * @param int  $id
     * @param bool $flag
     *
     * @return bool
     */
    public function approve($id, $flag = true)
    {
        $row = Pi::model('post', 'comment')->find($id);
        if (!$row) {
            return false;
        }
        if ((int) $row->active == (int) $flag) {
            return false;
        }
        $row->active = (int) $flag;
        $result = true;
        try {
            $row->save();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Enable/Disable comments for a target
     *
     * @param array|int $root
     * @param bool      $flag
     *
     * @return bool
     */
    public function enable($root, $flag = true)
    {
        $model = Pi::model('root', 'comment');
        if (is_int($root)) {
            $row = $model->find($root);
            if (!$row) {
                return false;
            }
        } else {
            $root = $this->canonizeRoot($root);
            $row = $model->select($root)->current();
            if (!$row) {
                $row = $model->createRow($root);
            }
        }
        if ($row->id && (int) $row->active == (int) $flag) {
            return false;
        }
        $row->active = (int) $flag;
        $result = true;
        try {
            $row->save();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Delete comment root and its comments
     *
     * @param int  $root
     *
     * @return bool
     */
    public function delete($root)
    {
        $row = Pi::model('root', 'comment')->find($root);
        if (!$row) {
            return false;
        }
        $result = true;
        Pi::model('post', 'comment')->delete(array('root' => $root));
        try {
            $row->delete();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
