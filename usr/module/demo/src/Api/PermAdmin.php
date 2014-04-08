<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi\Application\Api\AbstractApi;

class PermAdmin extends AbstractApi
{
    protected $module = 'demo';

    public function getResources()
    {
        $resources = array();

        for ($i = 1; $i <= 5; $i++) {
            $name = $this->module . '-resource-' . $i;
            $title = ucwords($this->module . ' resource admin ' . $i);
            $resources[$name] = $title;
        }

        //vd($resources);
        return $resources;
    }
}
