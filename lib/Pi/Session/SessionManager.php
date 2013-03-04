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

    protected $isValid;

    /**
     * Is this session valid?
     *
     * Notifies the Validator Chain until either all validators have returned
     * true or one has failed.
     *
     * @return bool
     */
    public function isValid()
    {
        if (null === $this->isValid) {
            $this->isValid = parent::isValid();
        }
        return $this->isValid;
    }

    /**
     * Write session to save handler and close
     *
     * Once done, the Storage object will be marked as isImmutable.
     *
     * @return void
     */
    public function writeClose()
    {
        // Skip storage writing if validation is failed
        if (!$this->isValid()) {
            $this->destroy();
            exit('Exit on session violation.');
        }

        // Set metadata for validators
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