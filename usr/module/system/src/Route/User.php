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
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * System user route
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
     * {@inheritDoc}
     */
    protected $structureDelimiter = '/';

    /**
     * {@inheritDoc}
     */
    protected function parseParams($path)
    {
        $path = $this->defaults['module'] . $this->structureDelimiter . $path;
        $matches = parent::parseParams($path);
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
    public function assemble(array $params = array(),
        array $options = array())
    {
        if (!$params) {
            return $this->prefix;
        }

        $url = parent::assemble($params, $options);
        $urlPrefix = $this->prefix . $this->paramDelimiter
            . $this->defaults['module'];
        $urlSuffix = substr($url, strlen($urlPrefix));
        $url = $this->prefix . $urlSuffix;
        return $url;
    }
}
