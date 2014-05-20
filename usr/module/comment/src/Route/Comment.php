<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Comment\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * Comment route
 *
 * Use cases:
 *
 * - Simplified URLs:
 *   - Item comment list: /list/<root-id> => List::Root
 *   - Comment post view: /post/<post-id> => Post::Index
 *
 * - Standard URLs:
 *   - Comment home: /  => Index::Index
 *   - Comment home: /list => List::Index
 *   - User comments: /list/user/id/<uid> => List::User
 *   - Module comments: /list/module/name/<module-name> => List::Module
 *   - Comment post submit: /post/submit => Post::Submit
 *   - Comment post delete: /post/delete/id/<post-id> => Post::Delete
 *   - Comment post approve: /post/approve/id/<post-id> => Post::approve
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Comment extends Standard
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'comment',
        'controller'    => 'index',
        'action'        => 'index'
    );

    /**
     * {@inheritDoc}
     */
    protected $structureDelimiter = '/';

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = null;

        $parts = array_filter(explode($this->structureDelimiter, $path));
        //$count = count($parts);
        if ($parts) {
            $term = array_shift($parts);
            // /list/<root-id>
            if ('list' == $term) {
                if ($parts && is_numeric($parts[0])) {
                    $matches = array(
                        'controller'    => 'list',
                        'action'        => 'root',
                        'root'          => (int) array_shift($parts),
                    );
                }

            // /post/<post-id>
            } elseif ('post' == $term) {
                if ($parts && is_numeric($parts[0])) {
                    $matches = array(
                        'controller'    => 'post',
                        'action'        => 'index',
                        'id'            => (int) array_shift($parts),
                    );
                }

            }

            if ($matches && $parts) {
                $matches = array_merge($matches, $this->parseParams($parts));
            }
        }

        if (null !== $matches) {
            $matches = array_merge($this->defaults, $matches);
        } else {
            $path = $this->defaults['module'] . $this->structureDelimiter . $path;
            $matches = parent::parse($path);
        }

        return $matches;
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return string
     */
    public function assemble(
        array $params = array(),
        array $options = array()
    ) {
        if (!$params) {
            return $this->prefix;
        }

        $url = null;

        $controller = isset($params['controller']) ? $params['controller'] : '';
        $action = isset($params['action']) ? $params['action'] : '';

        // /list/<root-id>
        if ('list' == $controller) {
            if ('' == $action || 'root' == $action) {
                if (!empty($params['root'])) {
                    $url .= 'list' . $this->paramDelimiter . $params['root'];
                    unset($params['root']);
                }
            }

        // /post/<post-id>
        } elseif ('post' == $controller) {
            if ('' == $action || 'index' == $action) {
                if (!empty($params['id'])) {
                    $url .= 'post' . $this->paramDelimiter . $params['id'];
                    unset($params['id']);
                }
            }
        }

        if ($url) {
            $part = $this->assembleParams($params);
            $url .= $part ? $this->paramDelimiter . $part : '';
            $url = $this->prefix . $this->paramDelimiter . $url;
        } else {
            $params['module'] = $this->defaults['module'];
            $url = parent::assemble($params, $options);
            $urlPrefix = $this->prefix . $this->paramDelimiter
                . $this->defaults['module'];
            $urlSuffix = substr($url, strlen($urlPrefix));
            $url = $this->prefix . $urlSuffix;
        }

        return $url;
    }
}
