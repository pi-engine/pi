<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Session\SaveHandler;

use Pi;
use Pi\Application\Model\Model;
use Zend\Session\SaveHandler\SaveHandlerInterface;
use Pi\Db\RowGateway\RowGateway;

/**
 * DB Table session save handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class DbTable implements SaveHandlerInterface, UserAwarenessInterface
{
    /**
     * Session table model
     * @var Model
     */
    protected $model;

    /**
     * Session data row
     * @var RowGateway
     */
    protected $row;

    /**
     * Session lifetime in seconds
     * @var int
     */
    protected $lifetime = 0;

    /**
     * Whether or not the lifetime of an existing session should be overridden
     * @var bool
     */
    protected $overrideLifetime = false;

    /**
     * Session save path, not used
     * @var string
     */
    protected $sessionSavePath;

    /**
     * Session name
     * @var string
     */
    protected $sessionName;

    /** @var  int User id */
    protected $uid;

    /**
     * Constructor
     *
     * @param array $config User-provided configuration
     */
    public function __construct($config = array())
    {
        if (empty($config['model'])) {
            $this->model = Pi::model('session');
        } elseif (is_string($config['model'])) {
            $this->model = Pi::model($config['model']);
        } else {
            $this->model = $config['model'];
        }
        $lifetime = isset($config['lifetime']) ? $config['lifetime'] : null;
        $this->setLifetime($lifetime);
        if (isset($config['overrideLifetime'])) {
            $this->setOverrideLifetime($config['overrideLifetime']);
        }
    }

    /**
     * Set session lifetime and optional whether or not the lifetime of
     * an existing session should be overridden
     *
     * $lifetime === false resets lifetime to session.gc_maxlifetime
     *
     * @param int       $lifetime
     * @param bool|null $overrideLifetime (optional)
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setLifetime($lifetime, $overrideLifetime = null)
    {
        if ($lifetime < 0) {
            throw new \InvalidArgumentException(
                'Lifetime must be greater than 0'
            );
        } elseif (empty($lifetime)) {
            $this->lifetime = (int) ini_get('session.gc_maxlifetime');
        } else {
            $this->lifetime = (int) $lifetime;
        }

        if ($overrideLifetime != null) {
            $this->setOverrideLifetime($overrideLifetime);
        }

        return $this;
    }

    /**
     * Retrieve session lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set whether or not the lifetime of an existing session should
     * be overridden
     *
     * @param bool $overrideLifetime
     * @return self
     */
    public function setOverrideLifetime($overrideLifetime)
    {
        $this->overrideLifetime = (boolean) $overrideLifetime;

        return $this;
    }

    /**
     * Retrieve whether or not the lifetime of an existing session
     * should be overridden
     *
     * @return bool
     */
    public function getOverrideLifetime()
    {
        return $this->overrideLifetime;
    }

    /**
     * Open Session
     *
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        $this->sessionSavePath = $savePath;
        $this->sessionName     = $name;

        return true;
    }

    /**
     * Close session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $return = '';
        if (!$id) {
            return $return;
        }
        $row = $this->model->find($id);
        if ($row) {
            $lifetime = $this->overrideLifetime
                ? $this->lifetime : $row->lifetime;
            if ($row->modified + $lifetime > time()) {
                $return = $row->data;
                $this->row = $row;
            } else {
                $this->destroy($id);
            }
        }

        return $return;
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return bool|int
     */
    public function write($id, $data)
    {
        $return = false;
        if (!$id) {
            return $return;
        }
        $row = ($this->row && $id == $this->row->id)
            ? $this->row : $this->model->find($id);
        $data = array(
            'modified'  => time(),
            'data'      => (string) $data,
        );
        if (null !== $this->uid) {
            $data['uid'] = (int) $this->uid;
        }

        try {
            if ($row) {
                $row->assign($data);
                $return = $row->save(false);
            } else {
                $data['id']         = $id;
                $data['lifetime']   = $this->lifetime;
                $row = $this->model->createRow($data);
                $return = $row->save(false);
            }
        } catch (\Exception $e) {
            trigger_error(
                'Session write error: ' . $e->getMessage(),
                E_USER_ERROR
            );
        }

        return $return;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $return = false;

        if ($this->model->delete(array('id' => $id))) {
            $return = true;
        }

        return $return;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        $this->model->delete($this->model->quoteIdentifier('modified') . ' + '
            . $this->model->quoteIdentifier('lifetime') . ' < '
            . $this->model->quoteValue(time()));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function setUser($uid)
    {
        $this->uid = (int) $uid;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function killUser($uid)
    {
        $result = null;
        if ($uid) {
            $row = null;
            try {
                $row = $this->model->find($uid, 'uid');
            } catch (\Exception $e) {
                $result = false;
            }
            if ($row) {
                try {
                    $row->delete();
                    $result = true;
                } catch (\Exception $e) {
                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve session lifetime
     *
     * @param RowGateway $row
     * @return int
     */
    protected function retrieveLifetime(RowGateway $row)
    {
        $return = $this->lifetime;

        if (!$this->overrideLifetime) {
            $return = (int) $row->lifetime;
        }

        return $return;
    }

    /**
     * Retrieve session expiration time
     *
     * @param RowGateway $row
     * @return int
     */
    protected function getExpirationTime(RowGateway $row)
    {
        return (int) $row->modified + $this->retrieveLifetime($row);
    }
}
