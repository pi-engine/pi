<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Route;

use Module\System\Route\User as UserRoute;

/**
 * User route
 *
 * Use cases:
 *
 * - Simplified URLs:
 *
 *   - Own home: / => Home::Index
 *   - Own home: /home => Home::Index
 *   - User home via ID: /$uid => Home::View
 *   - User home via ID: /home/$uid => Home::View
 *   - User home via identity: /home/identity/$user => Home::View
 *   - User home via name: /home/name/$user => Home::View
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
class User extends UserRoute
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'user',
        'controller'    => 'home',
        'action'        => 'index'
    );
}