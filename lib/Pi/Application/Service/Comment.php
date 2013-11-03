<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Db\Sql\Where;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;
use Module\Comment\Form\PostForm;

/**
 * Comment service
 *
 * - addPost(array $data)
 * - getPost($id)
 * - getRoot(array $condition|$id)
 * - getTarget($root)
 * - getList(array $condition|$root, $limit, $offset, $order)
 * - getCount(array $condition|$root)
 * - getForm(array $data)
 * - getUrl($type, array $options)
 * - updatePost($id, array $data)
 * - deletePost($id)
 * - approve($id, $flag)
 * - enable($root, $flag)
 * - delete($root, $flag)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Comment extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'comment';

    /** @var array TTL and CacheAdapter */
    protected $cache;

    /**
     * Is comment service available
     *
     * @return bool
     */
    public function active()
    {
        return Pi::service('module')->isActive('comment');
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getUrl($type, $options);
    }

    /**
     * Get comment post edit form
     *
     * @param array $data
     *
     * @return bool|PostForm
     */
    public function getForm(array $data = array())
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getForm($data);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->renderPost($post);
    }

    /**
     * Render list of posts
     *
     * @param array $posts
     * @param bool  $isAdmin
     *
     * @return array
     */
    public function renderList(array $posts, $isAdmin = false)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->renderList($posts, $isAdmin);
    }

    /**
     * Add comment of an item
     *
     * @param array $data   Data of uid, content, module, item, category, time
     *
     * @return int|bool
     */
    public function addPost(array $data)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->add($data);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getPost($id);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getRoot($condition);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getTarget($root);
    }

    /**
     * Get multiple comments
     *
     * @param int|array|Where $condition Root id or conditions
     * @param int       $limit
     * @param int       $offset
     * @param string    $order
     *
     * @return array|bool
     */
    public function getList($condition, $limit, $offset = 0, $order = '')
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getList($condition, $limit, $offset, $order);
    }

    /**
     * Get comment count
     *
     * @param int|array|Where     $condition Root id or conditions
     *
     * @return int|bool
     */
    public function getCount($condition)
    {
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->getCount($condition);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->update($id, $data);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->delete($id);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->approve($id, $flag);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->enable($root, $flag);
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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('comment')->deleteRoot($root);
    }

    /**
     * Get cache specs
     *
     * @param int $id
     *
     * @return array
     */
    public function cache($id = null)
    {
        if (null === $this->cache) {
            $ttl = $this->getOption('cache', 'ttl');
            $storage = $this->getOption('cache', 'storage');
            if ($ttl) {
                if ($storage) {
                    $storage = Pi::service('cache')->loadStorage($storage);
                } else {
                    $storage = null;
                }

                $this->cache = array(
                    'namespace' => 'comment',
                    'ttl'       => $ttl,
                    'storage'   => $storage,
                );
            } else {
                $this->cache = array();
            }
        }
        $spec = (array) $this->cache;
        if ($id && $spec) {
            $spec['key'] = md5((string) $id);
        }

        return $spec;
    }

    /**
     * Load comments on leading page from cache
     *
     * @param int $root
     *
     * @return array
     */
    public function loadCache($root)
    {
        $result = array();
        $cache = $this->cache($root);
        if ($root && $cache) {
            $data = Pi::service('cache')->getItem(
                $cache['key'],
                $cache,
                $cache['storage']
            );
            if (null !== $data) {
                $result = json_decode($data, true);
            }
        }

        return $result;
    }

    /**
     * Save comments on leading page to cache
     *
     * @param int   $root
     * @param array $data
     *
     * @return bool
     */
    public function saveCache($root, array $data)
    {
        $result = false;
        $cache = $this->cache($root);
        if ($root && $cache) {
            Pi::service('cache')->setItem(
                $cache['key'],
                json_encode($data),
                $cache,
                $cache['storage']
            );
            $result = true;
        }

        return $result;
    }

    /**
     * Flush cache for a root or all comments
     *
     * @param int|int[ $id
     * @param bool $isRoot
     *
     * @return bool
     */
    public function clearCache($id = null, $isRoot = false)
    {
        $result = false;

        if (!$id) {
            $cache = $this->cache();
            if ($cache) {
                Pi::service('cache')->clearByNamespace(
                    $cache['namespace'],
                    $cache['storage']
                );
                $result = true;
            }
        } else {
            $ids = (array) $id;
            foreach ($ids as $id) {
                if (!$isRoot) {
                    $post = $this->getPost($id);
                    if ($post) {
                        $id = $post['root'];
                    } else {
                        $id = 0;
                    }
                }
                if ($id) {
                    $cache = $this->cache($id);
                    // Remove an item
                    if ($cache) {
                        Pi::service('cache')->removeItem(
                            $cache['key'],
                            $cache,
                            $cache['storage']
                        );
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }
}
