<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Installer\Resource;

use Pi\Application\Installer\Resource\AbstractResource;

class Test extends AbstractResource
{
    public function installAction()
    {
        return [
            'status'  => true,
            'message' => sprintf(
                '%s: %s',
                __METHOD__,
                $this->config['config']
            ),
        ];
    }

    public function updateAction()
    {
        return [
            'status'  => true,
            'message' => sprintf(
                '%s: %s',
                __METHOD__,
                $this->config['config']
            ),
        ];
    }
}
