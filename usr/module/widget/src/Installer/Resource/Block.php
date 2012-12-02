<?php
/**
 * Pi module installer resoure
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\Widget
 * @subpackage      Installer
 * @version         $Id$
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
        Pi::service('registry')->block->clear($module);
        return true;
    }
}
