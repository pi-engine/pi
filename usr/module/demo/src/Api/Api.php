<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Api;

use Pi\Application\AbstractApi;

class Api extends AbstractApi
{
    protected $module = 'demo';

    public function test($args)
    {
        $result = sprintf('Method provider %s - %s: %s',
                          $this->module, __METHOD__, json_encode($args));

        return $result;
    }
}
