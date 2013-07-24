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

/**
 * User avatar abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAvatar
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Constructor
     *
     * @param UserModel $model
     */
    public function __construct(UserModel $model = null)
    {
        $this->model = $model;
    }

    /**
     * Get user avatar link
     *
     * @param string            $size           Size of image to display, integer for width, string for named size: 'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     * @return string
     */
    abstract public function build($size = '');
}
