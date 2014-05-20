<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Model;

use Pi;
use Pi\Application\Model\Model;

/**
 * Visit model class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Visit extends Model
{
    /**
     * Add a row
     *
     * @param int  $id  Article ID
     * @return array
     */
    public function addRow($id)
    {
        $user   = Pi::service('user')->getUser();
        $server = Pi::engine()->application()->getRequest()->getServer();
        $data   = array(
            'article'  => $id,
            'time'     => time(),
            'ip'       => $server['REMOTE_ADDR'],
            'uid'      => Pi::user()->getId() ?: 0,
        );
        $row    = $this->createRow($data);
        $result = $row->save();

        return $result;
    }
}
