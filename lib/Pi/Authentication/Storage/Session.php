<?php
/**
 * Session Manger
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Authentication
 * @subpackage      Storage
 * @version         $Id$
 */

namespace Pi\Authentication\Storage;
use Pi;
use Zend\Authentication\Storage\Session as ZendSession;

class Session extends ZendSession
{
    public function __construct($options = array())
    {
        $namespace  = $options['namespace'];
        $member     = $options['member'];
        $sessionManager = isset($options['session_manager']) ? $options['session_manager'] : Pi::service('session')->manager();
        parent::__construct($namespace, $member, $sessionManager);
    }
}
