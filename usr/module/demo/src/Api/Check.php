<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Api;

use Pi\Application\Api\AbstractApi;

class Check extends AbstractApi
{
    protected $module = 'demo';

    public function test($args)
    {
        $result = sprintf(
            'Method provider %s - %s: %s',
            $this->module,
            __METHOD__,
            json_encode($args)
        );

        return $result;
    }
}
