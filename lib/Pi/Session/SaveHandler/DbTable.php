<?php
/**
 * DB Table session save handler
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

namespace Pi\Session\SaveHandler;
use Pi;
use Pi\Application\Model\Model;
use Zend\Session\SaveHandler\SaveHandlerInterface;
use Pi\Db\RowGateway\RowGateway;

class DbTable implements SaveHandlerInterface
{
    /**
     * Session table model
     *
     * @var Model
     */
    protected $model;

    /**
     * Session data row
     *
     * @var RowGateway
     */
    protected $row;

    /**
     * Session lifetime in seconds
     *
     * @var int
     */
    protected $lifetime = 0;

    /**
     * Whether or not the lifetime of an existing session should be overridden
     *
     * @var boolean
     */
    protected $overrideLifetime = false;

    /**
     * Session save path
     *
     * @var string
     */
    protected $sessionSavePath;

    /**
     * Session name
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Constructor
     *
     * @param  array    $config      User-provided configuration
     * @return void
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
     * Set session lifetime and optional whether or not the lifetime of an existing session should be overridden
     *
     * $lifetime === false resets lifetime to session.gc_maxlifetime
     *
     * @param int $lifetime
     * @param boolean $overrideLifetime (optional)
     * @return DbTable
     */
    public function setLifetime($lifetime, $overrideLifetime = null)
    {
        if ($lifetime < 0) {
            throw new \InvalidArgumentException('Lifetime must be greater than 0');
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
     * Set whether or not the lifetime of an existing session should be overridden
     *
     * @param boolean $overrideLifetime
     * @return DbTable
     */
    public function setOverrideLifetime($overrideLifetime)
    {
        $this->overrideLifetime = (boolean) $overrideLifetime;

        return $this;
    }

    /**
     * Retrieve whether or not the lifetime of an existing session should be overridden
     *
     * @return boolean
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
     * @return boolean
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
     * @return boolean
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
            $lifetime = $this->overrideLifetime ? $this->lifetime : $row->lifetime;
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
     * @return boolean
     */
    public function write($id, $data)
    {
        $return = false;
        if (!$id) {
            return $return;
        }
        $row = ($this->row && $id == $this->row->id) ? $this->row : $this->model->find($id);
        $data = array('modified' => time(), 'data' => (string) $data);

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
            trigger_error('Session write error: ' . $e->getMessage(), E_USER_ERROR);
        }

        return $return;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
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
