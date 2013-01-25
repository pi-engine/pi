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
use Zend\Session\Storage\SessionStorage;
use Zend\Session\SaveHandler\SaveHandlerInterface;


class SessionManager extends ZendSessionManager
{
    protected $containers = array();

    /**
     * Start session
     *
     * if No session currently exists, attempt to start it. Calls
     * {@link isValid()} once session_start() is called, and raises an
     * exception if validation fails.
     *
     * @param bool $preserveStorage        If set to true, current session storage will not be overwritten by the
     *                                     contents of $_SESSION.
     * @return void
     * @throws Exception\RuntimeException
     */
    public function start($preserveStorage = false)
    {
        if ($this->sessionExists()) {
            return;
        }

        $saveHandler = $this->getSaveHandler();
        if ($saveHandler instanceof SaveHandlerInterface) {
            // register the session handler with ext/session
            $this->registerSaveHandler($saveHandler);
        }

        session_start();
        /*
        if (!$this->isValid()) {
            throw new Exception\RuntimeException('Session validation failed');
        }
        */
        $storage = $this->getStorage();

        // Since session is starting, we need to potentially repopulate our
        // session storage
        if ($storage instanceof SessionStorage && $_SESSION !== $storage) {
            if (!$preserveStorage) {
                $storage->fromArray($_SESSION);
            }
            $_SESSION = $storage;
        }

        if (!$this->isValid()) {
            throw new \RuntimeException('Session validation failed');
        }
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
        $validator = $this->getConfig()->getOption('validator');
        if ($validator) {
            $this->getStorage()->setMetaData('_VALID', $validator);
        }
        parent::writeClose();
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
