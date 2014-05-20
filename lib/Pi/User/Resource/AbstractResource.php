<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Resource;

use Pi;
use Pi\User\Adapter\AbstractAdapter;

/**
 * User resource handler abstraction
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AbstractResource
{
    /** @var  AbstractAdapter */
    protected $adapter;

    /** @var array Options */
    protected $options = array();

    /**
     * If user module available for time handling
     * @var bool|null
     */
    protected $isAvailable = null;

    /**
     * Constructor
     *
     * @param AbstractAdapter $adapter
     */
    public function __construct(AbstractAdapter $adapter = null)
    {
        $this->adapater = $adapter;
    }

    /**
     * Check if relation function available
     *
     * @return bool
     */
    protected function isAvailable()
    {
        if (null === $this->isAvailable) {
            $this->isAvailable = Pi::service('module')->isActive('user');
        }

        return $this->isAvailable;
    }

    /**
     * Set options
     *
     * @param array $options
     * @return self
     */
    public function setOptions($options = array())
    {
        $this->options = $options;

        return $this;
    }
}
