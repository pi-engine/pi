<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Resource;

use Pi;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\BindInterface;

/**
 * User resource handler abstraction
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AbstractResource implements BindInterface
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /** @var array Options */
    protected $options = array();

    /**
     * Bind a user
     *
     * @param UserModel|null $model
     * @return self
     */
    public function bind(UserModel $model = null)
    {
        $this->model = $model;

        return $this;
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
