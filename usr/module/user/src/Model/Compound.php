<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     * @param int $uid
     * @param string $compound
     * @param array $data
     *
     * @return bool
     */
    public function appendSet($uid, $compound, $data)
    {
        $select = $this->select();
        $select->columns([
            'maxset' => Pi::db()->expression(
                'MAX(' . $this->quoteIdentifier('set') . ')'
            ),
        ]);
        $select->where([
            'uid'      => $uid,
            'compound' => $compound,
        ]);
        $result = $this->selectWith($select);
        $maxSet = $result->current()->maxset;
        $set    = (int)$maxSet + 1;
        foreach ($data as $field => $value) {
            $specs = [
                'uid'      => $uid,
                'compound' => $compound,
                'set'      => $set,
                'field'    => $field,
                'value'    => $value,
            ];
            $row   = $this->createRow($specs);
            $row->save();
        }

        return true;
    }
}
