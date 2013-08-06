<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Installer\Resource;

use Pi\Application\Installer\Resource\AbstractResource;

class Test extends AbstractResource
{
    public function installAction()
    {
        return array(
            'status'    => true,
            'message'   => sprintf('%s: %s',
                                   __METHOD__,
                                  $this->config['config']),
        );
    }

    public function updateAction()
    {
        return array(
            'status'    => true,
            'message'   => sprintf('%s: %s',
                                   __METHOD__,
                                   $this->config['config']),
        );
    }
}
