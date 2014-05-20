<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * User route
 *
 * Use cases:
 *
 * - Simplified URLs:
 *
 *   - Feed: / => Index::Index
 *   - Feed with page: /page/$page => Index::Index
 *
 *   - Own home: /home => Home::Index
 *   - Own home with page: /home/page/$page => Home::Index
 *
 *   - User home via ID: /home/$uid => /home/$uid => Home::View
 *   - User home via ID with page: /home/$uid/page/$page => Home::View
 *   - User home via identity: /home/identity/$user => Home::View
 *   - User home via identity with page: /home/identity/$user/page/$page => Home::View
 *   - User home via name: /home/name/$user => Home::View
 *   - User home via name with page: /home/name/$user/page/$page => Home::View
 *
 *   - Own profile: /profile => Profile::Index
 *   - User profile via ID: /profile/$uid => Profile::View
 *   - User profile via identity: /profile/identity/$user => Profile::View
 *   - User profile via name: /profile/name/$user => Profile::View
 *
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

    protected $paramId = 'uid';
    protected $paramIdentity = 'identity';
    protected $paramName = 'name';
    protected $paramPage = 'page';

    /**
     * {@inheritDoc}
     */
    public function setOptions(array $options = array())
    {
        if (isset($options['param_id'])) {
            $this->paramId = $options['param_id'];
            unset($options['param_id']);
        }
        if (isset($options['param_identity'])) {
            $this->paramIdentity = $options['param_identity'];
            unset($options['param_identity']);
        }
        if (isset($options['param_name'])) {
            $this->paramName = $options['param_name'];
            unset($options['param_name']);
        }
        if (isset($options['param_page'])) {
            $this->paramPage = $options['param_page'];
            unset($options['param_page']);
        }
        parent::setOptions($options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = null;

        $parts = array();
        if ($path) {
            $parts = array_filter(explode($this->structureDelimiter, $path));
        }
        if ($parts) {
            $matches = array();
            $term = array_shift($parts);

            switch ($term) {
                // /home/<...>
                case 'home':
                    $matches['controller']  = 'home';
                    $matches['action']      = 'index';
                    if ($parts) {
                        // /home/<uid>
                        if (is_numeric($parts[0])) {
                            $matches['action'] = 'view';
                            $matches[$this->paramId] = (int) array_shift($parts);
                            // /home/identity/<...>
                        } elseif ($this->paramIdentity == $parts[0]
                            // /home/name/<...>
                            || $this->paramName == $parts[0]
                        ) {
                            $matches['action'] = 'view';
                        } else {
                            // Do nothing but leave to user own page
                        }
                    }
                    break;

                // /profile/<...>
                case 'profile':
                    $matches['controller']  = 'profile';
                    $matches['action']      = 'index';
                    if ($parts) {
                        // /profile/<id>
                        if (is_numeric($parts[0])) {
                            $matches['action'] = 'view';
                            $matches[$this->paramId] = (int) array_shift($parts);
                            // /profile/identity/<...>
                        } elseif ($this->paramIdentity == $parts[0]
                            // /profile/name/<...>
                            || $this->paramName == $parts[0]
                        ) {
                            $matches['action'] = 'view';
                            // /profile/<action>/<...>
                        } else {
                            $matches['action'] = array_shift($parts);
                        }
                    }
                    break;

                // /logout
                case 'logout':
                    $matches['controller']  = 'login';
                    $matches['action']      = 'logout';
                    break;

                default:
                    $matches = null;
                    break;
            }

            if (null !== $matches && $parts) {
                $matches = array_merge(
                    (array) $matches,
                    $this->parseParams($parts)
                );
            }
        }

        if (null !== $matches) {
            $matches = array_merge($this->defaults, $matches);
        } else {
            $path = $this->defaults['module'] . $this->structureDelimiter . $path;
            $matches = parent::parse($path);
        }
        // Transform id to uid
        if (isset($matches['id']) && 'id' != $this->paramId) {
            $matches[$this->paramId] = $matches['id'];
            unset($matches['id']);
        }

        return $matches;
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!$params) {
            return $this->prefix;
        }

        $url = null;

        // Transform uid to id
        if (isset($params[$this->paramId]) && 'id' != $this->paramId) {
            $params['id'] = $params[$this->paramId];
            unset($params[$this->paramId]);
        }
        $controller = isset($params['controller']) ? $params['controller'] : '';
        $action = isset($params['action']) ? $params['action'] : '';

        // /logout
        if ('logout' == $action) {
            $url = 'logout';

        // /<...>
        } elseif ('' == $controller || 'index' == $controller) {
            $url = '';

        // /home/<...>
        } elseif ('home' == $controller) {
            if ('' == $action || 'index' == $action || 'view' == $action) {
                // /home
                $url = 'home';
                // /home/<id>
                if (!empty($params['id'])) {
                    $url .= $this->paramDelimiter . $params['id'];
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

        if (null !== $url) {
            $part = $this->assembleParams($params);
            $url .= $part ? $this->paramDelimiter . $part : '';
            $url = $url
                ? $this->prefix . $this->paramDelimiter . $url
                : $this->prefix;
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
