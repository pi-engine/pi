<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Custom\User\Activity;

use Pi;

class Cndzz
{
    protected $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function get($uid, $limit, $offset = 0)
    {

        $uriBBS = 'http://bbs.cndzz.com/api/bbs_api.php?uid=1';
        $uriCndzz = 'http://www.cndzz.com/api/api.php';

        $dataBBS = json_decode(Pi::service('remote')->get($uriBBS, array(
            'uid'   => $uid
        )), true);

        $dataCndzz = json_decode(Pi::service('remote')->get($uriCndzz, array(
            'uid'   => $uid,
            'action'  => 'cndzz'
        )), true);

        d($dataBBS);


        return array_merge($dataBBS, $dataCndzz);
    }
}