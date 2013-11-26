<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Model;

use Pi;
use Pi\Application\Model\Model as BasicModel;

/**
 * User compound profile model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Compound extends BasicModel
{
    /**
     * Row gateway class
     *
     * @var string
     */
    protected $rowClass = 'Module\User\Model\RowGateway\Compound';

    /**
     * Append a compound set
     *
     * @param int       $uid
     * @param string    $compound
     * @param array     $data
     *
     * @return bool
     */
    public function appendSet($uid, $compound, $data)
    {
        $select = $this->select();
        $select->columns(array(
            'maxset'    => Pi::db()->expression(
                'MAX(' . $this->quoteIdentifier('set') . ')'
            )
        ));
        $select->where(array(
            'uid'       => $uid,
            'compound'  => $compound,
        ));
        $result = $this->selectWith($select);
        $maxSet = $result->current()->maxset;
        $set = (int) $maxSet + 1;
        foreach ($data as $field => $value) {
            $specs = array(
                'uid'       => $uid,
                'compound'  => $compound,
                'set'       => $set,
                'field'     => $field,
                'value'     => $value,
            );
            $row = $this->createRow($specs);
            $row->save();
        }

        return true;
    }
}
