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
 * - getTargetList(array $condition, $limit, $offset, $order)
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
        if (!array_key_exists('active', $data)) {
            $data['active'] = 1;
        } elseif (null === $data['active']) {
            unset($data['active']);
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
        if (!array_key_exists('active', $data)) {
            $data['active'] = 1;
        } elseif (null === $data['active']) {
            unset($data['active']);
        }
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
        //vd($routeMatch);
        // Look up root against route data
        $lookup = function ($data) use ($routeMatch) {
            $item = $routeMatch->getParam($data['identifier']);
            //vd($data['identifier']);
            //vd($item);
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
            //d($data);
            $item = $lookup($data);
            if ($item) {
                $root = array(
                    'module'    => $module,
                    'category'  => $key,
                    'item'      => $item,
                );
                break;
            }
        }
        //d($root);
        if (!$root) {
            return false;
        }

        $rootData = $this->getRoot($root);
        //vd($rootData['id']);
        $result = array(
            'root' => $rootData ?: $root,
            'count' => 0,
            'posts' => array(),
            'users' => array(),
            'url_list'  => '',
            'url_submit'    => Pi::service('url')->assemble(
                'comment',
                array('controller' => 'post', 'action' => 'submit')
            ),
            'url_ajax'  => Pi::service('url')->assemble(
                'comment',
                array('controller' => 'post', 'action' => 'ajax')
            ),
        );

        if ($rootData) {
            $result['count'] = $this->getCount($rootData['id']);

            //vd($result['count']);
            if ($result['count']) {
                $result['posts'] = $this->getList($rootData['id']);
                $result['url_list'] = Pi::service('url')->assemble(
                    'comment',
                    array(
                        'controller'    => 'list',
                        'action'        => 'index',
                        'id'            => $rootData['id']
                    )
                );
            }
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
     * @param array $data module, item, category, time
     *
     * @return int|bool
     */
    public function addRoot(array $data)
    {
        $rootData = $this->canonizeRoot($data);
        $row = Pi::model('root', 'comment')->createRow($rootData);
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
        $result = $row ? (array) $row : false;

        return $result;
    }

    /**
     * Get root
     *
     * @param int|array $condition
     *
     * @return array    Module, category, item, callback, active
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
        $target = Pi::model('category', 'comment')->select(array(
            'module'    => $rootData['module'],
            'name'      => $rootData['category'],
        ))->current();
        if (!$target) {
            return false;
        }
        $handler = new $target['callback']($rootData['module']);
        //$handler->setItem($rootData['item']);
        $result = $handler->get($rootData['item']);

        return $result;
    }

    /**
     * Get multiple targets being commented
     *
     * @param array|Where $condition
     * @param int         $limit
     * @param int         $offset
     * @param string      $order
     *
     * @return array List of targets indexed by root id
     */
    public function getTargetList(
        $condition,
        $limit          = 0,
        $offset         = 0,
        $order          = ''
    ) {
        $result = array();

        if ($condition instanceof Where) {
            $where = $condition;
        } else {
            $whereRoot = array();
            $wherePost = $this->canonizePost($condition);
            /*
            if (!isset($wherePost['active'])) {
                $wherePost['active'] = 1;
            }
            */
            if (isset($wherePost['active'])) {
                $whereRoot['active'] = $wherePost['active'];
            }
            if (isset($condition['module'])) {
                $whereRoot['module'] = $condition['module'];
            }
            if (isset($condition['category'])) {
                $whereRoot['category'] = $condition['category'];
            }

            $where = array();
            foreach ($wherePost as $field => $value) {
                $where['post.' . $field] = $value;
            }
            foreach ($whereRoot as $field => $value) {
                $where['root.' . $field] = $value;
            }
        }

        $limit = $limit ?: (Pi::config('comment_limit') ?: 10);
        $order = $order ?: 'post.time desc';
        $select = Pi::db()->select();
        $select->from(
            array('root' => Pi::model('root', 'comment')->getTable()),
            array('id', 'module', 'category', 'item')
        );
        $select->join(
            array('post' => Pi::model('post', 'comment')->getTable()),
            'post.root=root.id',
            array()
        );
        $select->group('post.root');
        $select->where($where)->order($order)->limit($limit);
        if ($offset) {
            $select->offset($offset);
        }
        $select->order($order);

        $targets = Pi::registry('category', 'comment')->read();

        $keyList = array();
        $rowset = Pi::db()->query($select);
        foreach ($rowset as $row) {
            $root = (int) $row['id'];
            $keyList[] = $root;
            $items[$row['module']][$row['category']][$row['item']] = $root;
        }
        foreach ($items as $module => $mList) {
            foreach ($mList as $category => $cList) {
                if (!isset($targets[$module][$category])) {
                    continue;
                }
                $callback = $targets[$module][$category]['callback'];
                $handler = new $callback($module);
                $targets = $handler->get(array_keys($cList));
                foreach ($targets as $item => $target) {
                    $root = $cList[$item];
                    $targetList[$root] = $target;
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
    public function getList($condition, $limit = 0, $offset = 0, $order = '')
    {
        $result = array();
        $limit = $limit ?: (Pi::config('comment_limit') ?: 10);

        $isJoin = false;
        if ($condition instanceof Where) {
            $where  = $condition;
            $isJoin = true;
        } else {
            $whereRoot = array();
            if (is_array($condition)) {
                $wherePost = $this->canonizePost($condition);
                //vd($wherePost);
                /*
                if (!isset($wherePost['active'])) {
                    $wherePost['active'] = 1;
                }
                */
                if (isset($condition['module'])) {
                    $whereRoot['module'] = $condition['module'];
                }
                if (isset($condition['category'])) {
                    $whereRoot['category'] = $condition['category'];
                }
                if (isset($whereRoot['module']) || isset($whereRoot['category'])) {
                    $isJoin = true;
                    if (isset($wherePost['active'])) {
                        $whereRoot['active'] = $wherePost['active'];
                    }
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
            $order = $order ?: 'time desc';
            $select = Pi::model('post', 'comment')->select();
        } else {
            $order = $order ?: 'post.time desc';
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

        $select->where($where)->limit($limit);
        if ($offset) {
            $select->offset($offset);
        }
        $select->order($order);
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
    public function getCount($condition)
    {
        $isJoin = false;
        if ($condition instanceof Where) {
            $where = $condition;
            $isJoin = true;
        } else {
            $whereRoot = array();
            if (is_array($condition)) {
                $wherePost = $this->canonizePost($condition);
                /*
                if (!array_key_exists('active', $wherePost)) {
                    $wherePost['active'] = 1;
                } elseif (null === $wherePost['active']) {
                    unset($wherePost['active']);
                }
                */
                if (isset($condition['module'])) {
                    $whereRoot['module'] = $condition['module'];
                }
                if (isset($condition['category'])) {
                    $whereRoot['category'] = $condition['category'];
                }
                if (isset($whereRoot['module']) || isset($whereRoot['category'])) {
                    $isJoin = true;
                    if (isset($wherePost['active'])) {
                        $whereRoot['active'] = $wherePost['active'];
                    }
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
     * Get URL to a root
     *
     * @param int $root
     *
     * @return string
     */
    public function getUrl($root)
    {
        $params = array('root' => $root);
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
