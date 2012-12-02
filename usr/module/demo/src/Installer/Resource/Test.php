<?php
/**
 * Demo module installer resource
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
 * @package         Module\Demo
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Module\Demo\Installer\Resource;
use Pi\Application\Installer\Resource\AbstractResource;

class Test extends AbstractResource
{
    public function installAction()
    {
        return array(
            'status'    => true,
            'message'   => sprintf('%s: %s', __METHOD__, $this->config['config'])
        );
    }

    public function updateAction()
    {
        return array(
            'status'    => true,
            'message'   => sprintf('%s: %s', __METHOD__, $this->config['config'])
        );
    }
}
