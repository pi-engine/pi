<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Handler;

use Pi;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\BindInterface;

/**
 * User external handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AbstractHandler implements BindInterface
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Bind a user
     *
     * @param UserModel $user
     * @return AbstractHandler
     */
    public function bind(UserModel $model = null)
    {
        $this->model = $model;
        return $this;
    }
}