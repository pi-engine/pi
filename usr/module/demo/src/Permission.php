<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo;

use Pi\Application\AbstractModuleAwareness;

class Permission extends AbstractModuleAwareness
{
    protected $module = 'demo';

    public function getResources()
    {
        $resources = array();
        for ($i = 1; $i <= 10; $i++) {
            $name = $this->module . '-resource-' . $i;
            $title = ucwords($this->module . ' resource ' . $i);
            $resources[$name] = $title;
        }

        return $resources;
    }
}
