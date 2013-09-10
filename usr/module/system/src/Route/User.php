<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * User route
 *
 * Use cases:
 *
 * - Simplified URLs:
 *   - Own home: / => Home::Index
 *   - Own home: /home => Home::Index
 *   - User home via ID: /$uid => Home::View
 *   - User home via ID: /home/$uid => Home::View
 *   - User home via identity: /home/identity/$user => Home::View
 *   - User home via name: /home/name/$user => Home::View
 *   - Own profile: /profile => Profile::Index
 *   - User profile via ID: /profile/$uid => Profile::View
 *   - User profile via identity: /profile/identity/$user => Profile::View
 *   - User profile via name: /profile/name/$user => Profile::View
 *   - Logout: /logout  => Login::logout
 *
 * - Standard URLs:
 *   - Login: /login => Login::index
 *   - Login process: /login/process => Login::process
 *   - Register: /register  => Register::index
 *   - Register process: /register/process => Register::process
 *   - Register finish: /register/finish => Register::finish
 *   - Change email: /email => Email::index
 *   - Find password: /password => Password::index
 *   - Personal account: /account => Account::index
 *   - Personal account edit: /account/edit => Account::Edit
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends Standard
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'system',
        'controller'    => 'home',
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
            // /logout
            if ('logout' == $term) {
                $matches['controller'] = 'login';
                $matches['action'] = 'logout';

            // /<id>
            } elseif (is_numeric($term)) {
                $matches['controller'] = 'home';
                $matches['action'] = 'view';
                $matches['id'] = (int) $term;

            // /home/<...>
            } elseif ('home' == $term) {
                $matches['controller'] = 'home';
                // /home
                if (!$parts) {
                    $matches['action'] = 'index';
                } else {
                    $matches['action'] = 'view';
                    // /home/<id>
                    if (is_numeric($parts[0])) {
                        $matches['id'] = (int) array_shift($parts);
                    }
                }

            // /profile/<...>
            } elseif ('profile' == $term) {
                $matches['controller'] = 'profile';
                // /profile
                if (!$parts) {
                    $matches['action'] = 'index';
                } else {
                    // /profile/<id>
                    if (is_numeric($parts[0])) {
                        $matches['action'] = 'view';
                        $matches['id'] = (int) array_shift($parts);
                    // /profile/name/<name>
                    // /profile/identity/<identity>
                    } elseif ('name' == $parts[0] || 'identity' == $parts[0]) {
                        $matches['action'] = 'view';
                    // /profile/<action>/<...>
                    } else {
                        $matches['action'] = array_shift($parts);
                    }
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
        // Transform id to uid
        if (isset($matches['id'])) {
            $matches['uid'] = $matches['id'];
            unset($matches['id']);
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

        // Transform uid to id
        if (isset($params['uid'])) {
            $params['id'] = $params['uid'];
            unset($params['uid']);
        }
        $controller = isset($params['controller']) ? $params['controller'] : '';
        $action = isset($params['action']) ? $params['action'] : '';

        // /logout
        if ('logout' == $action) {
            $url = 'logout';

        // /home/<...>
        } elseif ('' == $controller || 'home' == $controller) {
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
