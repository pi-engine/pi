<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Installer\Resource;

use Pi;
use Pi\Application\Installer\Resource\Block as BasicBlock;

class Block extends BasicBlock
{
    /**
     * Overwrite regular Block updater to avoid block deletion
     *
     * @return boolean
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::registry('block')->clear($module);

        return true;
    }
}
