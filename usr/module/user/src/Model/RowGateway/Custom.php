<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model\RowGateway;

use Pi;
//use Pi\Db\RowGateway\RowGateway;

/**
 * User custom profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Custom extends AbstractFieldRowGateway
{
    /** @var string Model type */
    protected static $type = 'custom';

    /**
     * Get value of a column for display
     *
     * @param string $col
     * @return string|mixed[]
     */
    public function display($col = null)
    {
        $col = $this->field;
        return parent::display($col);
    }

    /**
     * Transform a meat
     *
     * @param string $key
     * @return mixed
     */
    protected function transformMeta($key)
    {
        $key = 'value';
        return parent::transformMeta($key);
    }

}
