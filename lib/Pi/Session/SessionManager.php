<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Session;

use Pi\Session\SaveHandler\UserAwarenessInterface;
use Zend\Session\SessionManager as ZendSessionManager;
use Zend\Session\Container;

/**
 * Session manager
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class SessionManager extends ZendSessionManager implements
    UserAwarenessInterface
{
    /**
     * Default options when a call to {@link destroy()} is made
     *
     * - send_expire_cookie: whether or not to send a cookie expiring
     *      the current session cookie;
     * - clear_storage: whether or not to empty the storage object of
     *      any stored values.
     * @var array
     */
    protected $defaultDestroyOptions = array(
        'send_expire_cookie' => true,
        'clear_storage'      => true,
    );

    /** @var array Session containers */
    protected $containers = array();

    /** @var array Validator */
    protected $validators = array();

    /** @var bool Session is valid */
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
            //$this->destroy();
            return;
        }

        // Set metadata for validators
        $storage = $this->getStorage();
        if (!$storage->isImmutable() && $this->validators) {
            $storage->setMetaData('_VALID', $this->validators);
        }

        parent::writeClose();
    }

    /**
     * Set validators
     *
     * @param array $validators
     * @return self
     */
    public function setValidators($validators = array())
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * Get container
     *
     * @param string $name
     * @return Container
     */
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
     * @return self
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

    /**
     * {@inheritDoc}
     */
    public function setUser($uid)
    {
        $saveHandler = $this->getSaveHandler();
        if ($saveHandler instanceof UserAwarenessInterface) {
            $result = $saveHandler->setUser($uid);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function killUser($uid)
    {
        $result = null;
        $saveHandler = $this->getSaveHandler();
        if ($saveHandler instanceof UserAwarenessInterface) {
            $result = $saveHandler->killUser($uid);
        }

        return $result;
    }
}
