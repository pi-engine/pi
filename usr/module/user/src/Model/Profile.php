<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model;

use Pi\Application\Model\Model as BasicModel;

/**
 * User profile model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Profile extends BasicModel
{
    /**
     * Class name for row
     *
     * @var string
     */
    protected $rowClass = 'Module\User\Model\RowGateway\Profile';
}
