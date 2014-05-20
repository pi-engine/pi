<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
     * Load leading comment posts for targets
     *
     * Load comment data
     * - root[id, module, category, item, active]
     * - count
     * - posts[id, uid, IP, content, time, active]
     * - users[uid, name, avatar, url]
     * - url_list
     * - url_submit
     * - url_ajax
     *
     * @param array|string $params
     *
     * @return string
     */
    public function load($params = null)
    {
        if (!$this->active()) {
            return;
        }
        if (is_array($params) && isset($params['options']['type'])) {
            $type = $params['options']['type'];
            unset($params['options']['type']);
        } else {
            $type = '';
        }

        if ('js' == $type) {
            $callback = Pi::service('url')->assemble('comment', array(
                'module'        => 'comment',
                'controller'    => 'index',
                'action'        => 'load',
            ));
            $content =<<<EOT
<div id="pi-comment-lead" style="display: none;"></div>
<script>
    $.getJSON("{$callback}", {
        uri: $(location).attr('href'),
        time: new Date().getTime()
    })
    .done(function (data) {
        if (data.content) {
            var el = $('#pi-comment-lead');
            el.show().html(data.content);
        }
    });
</script>
EOT;
        } else {
            $content = $this->loadContent($params);
            $content = '<div id="pi-comment-lead">' . $content . '</div>';
        }

        return $content;
    }

    /**
     * Load leading comment content
     *
     * @param array|string $params
     *
     * @return string
     */
    public function loadContent($params = null)
    {
        $options = array();
        if (is_string($params)) {
            $uri = $params;
            $routeMatch = Pi::service('url')->match($uri);
            $params = array('uri' => $uri);
        } else {
            $routeMatch = Pi::engine()->application()->getRouteMatch();
            $params = (array) $params;
            if (isset($params['options'])) {
                $options = $params['options'];
                unset($params['options']);
            }
        }
        $params = array_replace($params, $routeMatch->getParams());
        $data = Pi::api('api', 'comment')->load($params, $options);
        if (!$data) {
            return;
        }
        $data['uri'] = isset($params['uri'])
            ? $params['uri']
            : Pi::service('url')->getRequestUri();
        $data['uid'] = Pi::user()->getId();
        $template = 'comment:front/comment-lead';
        $result = Pi::service('view')->render($template, $data);

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
        if (!$this->active()) {
            return false;
        }

        return Pi::api('api', 'comment')->getUrl($type, $options);
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

        return Pi::api('api', 'comment')->getForm($data);
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

        return Pi::api('api', 'comment')->renderPost($post);
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

        return Pi::api('api', 'comment')->renderList($posts, $isAdmin);
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

        return Pi::api('api', 'comment')->add($data);
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

        return Pi::api('api', 'comment')->getPost($id);
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

        return Pi::api('api', 'comment')->getRoot($condition);
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

        return Pi::api('api', 'comment')->getTarget($root);
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

        return Pi::api('api', 'comment')->getList($condition, $limit, $offset, $order);
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

        return Pi::api('api', 'comment')->getCount($condition);
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

        return Pi::api('api', 'comment')->update($id, $data);
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

        return Pi::api('api', 'comment')->delete($id);
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

        return Pi::api('api', 'comment')->approve($id, $flag);
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

        return Pi::api('api', 'comment')->enable($root, $flag);
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

        return Pi::api('api', 'comment')->deleteRoot($root);
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
     * @param int|int[] $id
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

    /**
     * Insert user timeline for a new comment
     *
     * @param int $id
     * @param int $uid
     *
     * @return bool
     */
    public function timeline($id, $uid = null)
    {
        $result = true;
        $uid = $uid ?: Pi::service('user')->getId();

        $message = __('Posted a new comment.');
        $link = Pi::url(Pi::api('api', 'comment')->getUrl('post', array(
            'post'      => $id,
        )), true);
        $params = array(
            'uid'       => $uid,
            'message'   => $message,
            'timeline'  => 'new_comment',
            'time'      => time(),
            'module'    => 'comment',
            'link'      => $link,
        );
        Pi::service('user')->timeline()->add($params);

        return $result;
    }
}
