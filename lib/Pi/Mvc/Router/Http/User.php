<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Router\Http;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * User route for Pi Engine
 *
 * Use cases:
 *
 *  1. Login: user/login => Login::index
 *  2. Login process: user/login/process => Login::process
 *  3. Logout: user/logout  => Login::logout
 *  4. Register: user/register  => Register::index
 *  5. Register process: user/register/process => Register::process
 *  6. Register finish: user/register/finish => Register::finish
 *  7. Change email: user/email => Email::index
 *  8. Find password: user/password => Password::index
 *  9. User profile via ID: user/profile/$uid => Profile::index
 * 10. User profile via identity: user/profile/$user => Profile::index
 * 11. Personal account: user/account => Account::index
 * 12. Personal account edit: user/account/edit => Account::Edit
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends Standard
{
    /** @var string */
    protected $prefix = '/user';

    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'system',
        'controller'    => 'account',
        'action'        => 'index'
    );

    /**
     * match(): defined by Route interface.
     *
     * @see    Route::match()
     * @param Request $request
     * @param int|null  $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        $matches = array();
        list($path, $pathLength) = $result;

        $params  = $path ? explode($this->paramDelimiter, $path) : array();
        if ($params) {
            $param = array_shift($params);
            // Pre-check for logout
            if ('logout' == $param) {
                $matches = array(
                    'controller'    => 'login',
                    'action'        => 'logout',
                );
            } else {
                $matches['controller'] = $param;
                if (!empty($params)) {
                    $param = array_shift($params);
                    // Check for profile
                    if ('profile' == $matches['controller']) {
                        $matches['id'] = urldecode($param);
                    } else {
                        $matches['action'] = $param;
                    }
                }
            }
        }

        return new RouteMatch(array_merge($this->defaults, $matches), $pathLength);
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return string
     */
    public function assemble(array $params = array(), array $options = array())
    {
        if (!$params) {
            return $this->prefix;
        }
        $mergedParams = array_merge($this->defaults, $params);
        if ('login' == $mergedParams['controller'] && 'logout' == $mergedParams['action']) {
            $url = 'logout';
        } else {
            $url = urlencode($mergedParams['controller']);
            if ($this->defaults['action'] != $mergedParams['action']) {
                $url .= ($url ? $this->paramDelimiter : '') . urlencode($mergedParams['action']);
            }
        }
        if ('profile' == $mergedParams['controller']) {
            if (!empty($mergedParams['id'])) {
                $url .= $this->paramDelimiter . intval($mergedParams['id']);
            } elseif (!empty($mergedParams['identity'])) {
                $url .= $this->paramDelimiter . urlencode($mergedParams['identity']);
            }
        }

        return $this->paramDelimiter . trim($this->prefix, $this->paramDelimiter) . ($url ? $this->paramDelimiter . $url : '');
    }
}
