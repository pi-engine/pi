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
 * @package         Pi\Session
 * @version         $Id$
 */

namespace Pi\Session;

use Zend\Session\SessionManager as ZendSessionManager;
use Zend\Session\Container;

class SessionManager extends ZendSessionManager
{
    protected $containers = array();
    protected $validators = array();

    /**
     * Write session to save handler and close
     *
     * Once done, the Storage object will be marked as isImmutable.
     *
     * @return void
     */
    public function writeClose()
    {
        // The assumption is that we're using PHP's ext/session.
        // session_write_close() will actually overwrite $_SESSION with an
        // empty array on completion -- which leads to a mismatch between what
        // is in the storage object and $_SESSION. To get around this, we
        // temporarily reset $_SESSION to an array, and then re-link it to
        // the storage object.
        //
        // Additionally, while you _can_ write to $_SESSION following a
        // session_write_close() operation, no changes made to it will be
        // flushed to the session handler. As such, we now mark the storage
        // object isImmutable.
        $storage  = $this->getStorage();
        if (!$storage->isImmutable() && $this->validators) {
            $storage->setMetaData('_VALID', $this->validators);
        }
        parent::writeClose();
    }

    public function setValidators($validators = array())
    {
        $this->validators = $validators;
        return $this;
    }

    public function container($name = 'Default')
    {
        if (!isset($this->containers[$name])) {
            $this->containers[$name] = new Container($name, $this);
        }
        return $this->containers[$name];
    }

    /**
     * Set the TTL (in seconds) for the session cookie expiry
     *
     * Can safely be called in the middle of a session.
     *
     * @param  null|int $ttl
     * @return SessionManager
     */
    public function rememberMe($ttl = null)
    {
        if (null === $ttl) {
            $ttl = $this->getConfig()->getRememberMeSeconds();
        }
        $this->setSessionCookieLifetime($ttl);
        $this->saveHandler->setLifetime($ttl);
        return $this;
    }
}