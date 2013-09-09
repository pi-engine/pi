<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Api;

use Pi;
use Pi\Application\AbstractApi;
use Pi\Db\Sql\Where;
use Zend\Mvc\Router\RouteMatch;

/**
 * Comment manipulation APIs
 *
 * - load($routeMatch)
 * - add($root, array $data)
 * - addRoot(array $data)
 * - get($id)
 * - getRoot(array $condition|$id)
 * - getTarget($root)
 * - getList(array $condition|$root, $limit, $offset, $order)
 * - getCount(array $condition|$root)
 * - getUrl($root, $id)
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
        'uid',
        'ip',
        'time',
        'content',
        'active'
    );

    /** @var string[] Comment root table columns */
    protected $rootColumn = array(
        'id',
        'module',
        'category',
        'item',
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
        foreach ($data as $key => $value) {
            if (in_array($key, $this->postColumn)) {
                $result[$key] = $value;
            }
        }

        return $data;
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
        foreach ($data as $key => $value) {
            if (in_array($key, $this->rootColumn)) {
                $result[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Load comment data for rendering against matched route
     *
     * Data array:
     * - root: [id, ]module, category, item, active
     * - count
     * - posts: id, uid, ip, content, time, active
     * - users: uid, name, avatar, url
     * - url_list
     * - url_submit
     * - url_ajax
     *
     * @param RouteMatch $routeMatch
     *
     * @return array|bool
     */
    public function load(RouteMatch $routeMatch)
    {
        $module = $routeMatch->getParam('module');
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        $categoryList = Pi::registry('category', 'comment')->read($module);
        if (!isset($categoryList[$controller][$action])) {
            return false;
        }
        // Look up root against route data
        $lookup = function ($data) use ($routeMatch) {
            $item = $routeMatch->getParam($data['identifier']);
            if (null === $item) {
                return false;
            }
            if ($data['params']) {
                foreach ($data['params'] as $param => $value) {
                    if ($value != $routeMatch->getParam($param)) {
                        return false;
                    }
                }
            }

            return $item;
        };

        $root = array();
        foreach ($categoryList[$controller][$action] as $key => $data) {
            $item = $lookup($data);
            if ($item) {
                $root = array(
                    'module'    => $module,
                    'category'  => $key,
                    'item'      => $item,
                );
            }
        }
        if (!$root) {
            return false;
        }
        $rootData = $this->getRoot($root);
        $result = array(
            'root' => $rootData ?: $root,
            'count' => 0,
            'posts' => array(),
            'users' => array(),
            'url_list'  => '',
            'url_submit'    => Pi::service('url')->assemble(
                'comment',
                array('action' => 'submit')
            ),
            'url_ajax'  => Pi::service('url')->assemble(
                'comment',
                array('action' => 'ajax')
            ),
        );

        if ($rootData) {
            $result['count'] = $this->getRoot($rootData['id']);
            if ($result['count']) {
                $result['posts'] = $this->getList($rootData['id']);
            }
            $result['url_list'] = Pi::service('url')->assemble(
                'comment',
                array('action' => 'list', 'root' => $rootData['id'])
            );
        }
        $users = array();
        foreach ($result['posts'] as $post) {
            $users[$post['uid']] = array();
        }
        if ($users) {
            $avatars = Pi::avatar()->getList(array_keys($users));
            $names = Pi::user()->get(array_keys($users), 'name');
            foreach ($users as $uid => &$user) {
                $user = array(
                    'name'      => $names[$uid],
                    'avatar'    => $avatars[$uid],
                    'url'       => Pi::user()->getUrl('profile', $uid),
                );
            }
            $result['users'] = $users;
        }

        return $result;
    }

    /**
     * Add comment of an item
     *
     * @param array $data root, uid, content, module, item, category, time
     *
     * @return int|bool
     */
    public function addPost(array $data)
    {
        $postData = $this->canonizePost($data);
        if (empty($postData['root'])) {
            $root = $this->addRoot($data);
            if (!$root) {
                return false;
            }
            $postData['root'] = $root;
        }
        if (!isset($postData['time'])) {
            $postData['time'] = time();
        }
        $row = Pi::model('post', 'comment')->createRow($postData);
        $id = $row->save();

        return $id;
    }

    /**
     * Add comment root of an item
     *
     * @param array $data module, item, category, time
     *
     * @return int|bool
     */
    public function addRoot(array $data)
    {
        $rootData = $this->canonizeRoot($data);
        if (!isset($rootData['time'])) {
            $rootData['time'] = time();
        }
        $row = Pi::model('root', 'comment')->createRow($rootData);
        $id = $row->save();

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
        $result = $row ? (array) $row : false;

        return $result;
    }

    /**
     * Get root
     *
     * @param int|array $condition
     *
     * @return array|bool    module, category, item, callback, active
     */
    public function getRoot($condition)
    {
        if (is_int($condition)) {
            $row = Pi::model('root', 'comment')->find($condition);
            $result = $row ? (array) $row : false;
        } else {
            $rowset = Pi::model('root', 'comment')->select(
                $this->canonizeRoot($condition)
            );
            if ($rowset->count() == 1) {
                $result = (array) $rowset->current();
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get target content
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
        $handler = new $rootData['callback']($rootData['module']);
        $handler->setRoot($rootData);
        //$result = $handler->get();
        $result = array(
            'title' => $handler->get('title'),
            'url'   => $handler->get('url'),
            'uid'   => $handler->get('uid'),
            'time'  => $handler->get('time'),
        );

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
    public function getList($condition, $limit = 0, $offset = 0, $order = '')
    {
        $limit = $limit ?: Pi::config('comment_limit');
        $order = $order ?: 'time desc';
        $select = Pi::model('post', 'comment')->select();
        if ($condition instanceof Where) {
            $where = $condition;
        } elseif (is_array($condition)) {
            $where = $this->canonizePost($condition);
        } else {
            $where = array(
                'root'      => (int) $condition,
                'active'    => 1,
            );
        }
        $select->where($where)->order($order)->limit($limit);
        if ($offset) {
            $select->offset($offset);
        }
        $rowset = Pi::model('post', 'comment')->selectWith($select);
        $result = array();
        foreach ($rowset as $row) {
            $result[] = (array) $row;
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
    public function getCount($condition)
    {
        $select = Pi::model('post', 'comment')->select();
        if ($condition instanceof Where) {
            $where = $condition;
        } elseif (is_array($condition)) {
            $where = $this->canonizePost($condition);
        } else {
            $where = array(
                'root'      => (int) $condition,
                'active'    => 1,
            );
        }
        $select->where($where);
        $select->columns(array(
            'count' => Pi::db()->expression('COUNT(*)')
        ));
        $row = Pi::model('post', 'comment')->selectWith($select)->current();
        $result = $row ? (int) $row['count'] : false;

        return $result;
    }

    /**
     * Get URL to a comment or a comment root
     *
     * @param int $root
     * @param int $id
     *
     * @return string
     */
    public function getUrl($root, $id = null)
    {
        $params = array('root' => $root);
        if ($id) {
            $params['id'] = $id;
        }
        $url = Pi::service('url')->assemble('comment', $params);

        return $url;
    }

    /**
     * Update a comment
     *
     * @param int   $id
     * @param array $data
     *
     * @return bool
     */
    public function updatePost($id, array $data)
    {
        $row = Pi::model('post', 'comment')->find($id);
        if (!$row) {
            return false;
        }

        $data = $this->canonizePost($data);
        if (!isset($data['time_updated'])) {
            $data['time_updated'] = time();
        }
        $result = true;
        try {
            $row->assign($data)->save();
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
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
