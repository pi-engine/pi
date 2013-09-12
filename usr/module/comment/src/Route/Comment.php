<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Comment\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * User route
 *
 * Use cases:
 *
 * - Simplified URLs:
 *   - Item comments: /<root-id> => List::Root
 *   - User comments: /user/<uid> => List::User
 *   - Module comments: /module/<module-name> => List::Module
 *   - Module category comments: /module/<module-name>/<category> => List::Module
 *
 * - Standard URLs:
 *   - Comment home: /  => List::Index
 *   - Comment home: /list => List::Index
 *   - Comment post view: /post/view/<post-id> => Post::Index
 *   - Comment post submit: /post/submit => Post::Submit
 *   - Comment post delete: /post/delete => Post::Delete
 *   - Comment post approve/disapprove: /post/approve => Post::approve
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
        'controller'    => 'list',
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
        $count = count($parts);
        if ($count) {
            $term = array_shift($parts);
            // /<id>
            if (is_numeric($term)) {
                $matches['controller'] = 'list';
                $matches['action'] = 'root';
                $matches['root'] = (int) $term;

            // /user
            // /user/<uid>
            } elseif ('user' == $term) {
                $matches['controller'] = 'list';
                $matches['action'] = 'user';
                if ($parts && is_numeric($parts[0])) {
                    $matches['uid'] = (int) array_shift($parts);
                }

            // /module/<...>
            } elseif ('module' == $term && $parts) {
                $matches['controller'] = 'list';
                $matches['action'] = 'module';
                // /module/<module-name>
                $matches['name'] = array_shift($parts);
                // /module/<module-name>/<category-name>
                if ($parts) {
                    $matches['category'] = array_shift($parts);
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

        if ('' == $controller || 'list' == $controller) {
            if ('' == $action || 'index' == $action || 'view' == $action) {
                // /home
                $url = 'home';
                if (!empty($params['id'])) {
                    // /home/<id>
                    if (count($params) > 1) {
                        $url .= $this->paramDelimiter . $params['id'];
                    // /<id>
                    } else {
                        $url = $params['id'];
                    }
                    unset($params['id']);
                }
            }
        // /profile/<...>
        } elseif ('profile' == $controller) {
            if ('' == $action || 'index' == $action || 'view' == $action) {
                // /profile
                $url = 'profile';
                // /profile/<id>
                if (!empty($params['id'])) {
                    $url .= $this->paramDelimiter . $params['id'];
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
