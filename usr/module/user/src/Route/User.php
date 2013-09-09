<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * User route
 *
 * Use cases:
 *
 * - Standard URLs:
 *   - Login: user/login => Login::index
 *   - Login process: user/login/process => Login::process
 *   - Register: user/register  => Register::index
 *   - Register process: user/register/process => Register::process
 *   - Register finish: user/register/finish => Register::finish
 *   - Change email: user/email => Email::index
 *   - Find password: user/password => Password::index
 *   - Personal account: user/account => Account::index
 *   - Personal account edit: user/account/edit => Account::Edit
 *
 * - Simplified URLs:
 *   - User home via ID: user/view/$uid => Profile::Home
 *   - User home via ID: user/home/$uid => Profile::Home
 *   - User home via identity: user/view/identity/$user => Profile::Home
 *   - User home via identity: user/home/identity/$user => Profile::Home
 *   - User home via name: user/view/name/$user => Profile::Home
 *   - User home via name: user/home/name/$user => Profile::Home
 *   - User profile via ID: user/account/$uid => Profile::index
 *   - User profile via identity: user/account/identity/$user => Profile::index
 *   - User profile via name: user/account/name/$user => Profile::index
 *   - Logout: user/logout  => Login::logout
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
        'module'        => 'user',
        'controller'    => 'profile',
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
            if ('logout' == $term) {
                $matches['controller'] = 'login';
                $matches['action'] = 'logout';
            } elseif ('view' == $term || 'home' == $term) {
                $matches['controller'] = 'profile';
                $matches['action'] = 'home';
            } elseif ('account' == $term) {
                $matches['controller'] = 'profile';
                $matches['action'] = 'index';
            } elseif (is_numeric($term)) {
                $matches['controller'] = 'profile';
                $matches['action'] = 'index';
                $matches['id'] = (int) $term;
            }
            if ($matches) {
                $matches = array_merge($matches, $this->parseParams($parts));
            }
        }

        if (null !== $matches) {
            $matches = array_merge($this->defaults, $matches);
        } else {
            $path = $this->defaults['module'] . $this->structureDelimiter . $path;
            $matches = parent::parse($path);
            //vd($path);
            //vd($matches);
        }
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

        //vd($params);
        $url = null;
        if (isset($params['uid'])) {
            $params['id'] = $params['uid'];
            unset($params['uid']);
        }
        $controller = isset($params['controller']) ? $params['controller'] : '';
        $action = isset($params['action']) ? $params['action'] : '';
        if ('logout' == $action) {
            $url = 'logout';
        } elseif ('view' == $action
            || 'view' == $controller
            || 'home' == $action
            || 'home' == $controller
            || ('profile' == $controller && 'home' == $action)
        ) {
            $url = 'view';
            if (isset($params['id'])) {
                $url .= $this->paramDelimiter . $params['id'];
                unset($params['id']);
            }
        } elseif ('account' == $action
            || 'account' == $controller
            || ('profile' == $controller && ('index' == $action || '' == $action))
        ) {
            $url = 'account';
            if (isset($params['id'])) {
                $url .= $this->paramDelimiter . $params['id'];
                unset($params['id']);
            }
        }
        //vd($url);

        if ($url) {
            $part = $this->assembleParams($params);
            $url .= $part ? $this->paramDelimiter . $part : '';
            $url = $this->prefix . $this->paramDelimiter . $url;
            //vd($url);
        } else {
            $params['module'] = $this->defaults['module'];
            $url = parent::assemble($params, $options);
            $urlPrefix = $this->prefix . $this->paramDelimiter
                       . $this->defaults['module'];
            $urlSuffix = substr($url, strlen($urlPrefix));
            $url = $this->prefix . $urlSuffix;
            //vd($url);
        }

        return $url;
    }
}
