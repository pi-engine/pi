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
 * User compound profile row gateway
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Compound extends AbstractFieldRowGateway
{
    /** @var string Model type */
    protected $type = 'compound';

    /**
     * {@inheritDoc}
     */
    protected function getMeta($key = null)
    {
        if (!isset($this->meta)) {
            $this->meta = Pi::registry('compound', 'user')->read(
                $this['compound']
            );
        }
        $key = $this['field'];
        return parent::getMeta($key);
    }

    /**
     * {@inheritDoc}
     */
    public function filter($col = null)
    {
        $col = $this['field'];
        return parent::filter($col);
    }

    /**
     * {@inheritDoc}
     */
    protected function filterField($field)
    {
        $field = 'value';
        return parent::filterField($field);
    }
}
