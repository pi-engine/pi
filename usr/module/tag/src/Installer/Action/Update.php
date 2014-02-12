<?php
/**
 * Xoops module installer action
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
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Module\Tag\Installer\Action;

use Pi\Application\Installer\Action\Update as BasicUpdate;
use Module\Tag\Installer\Schema;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.pre', array($this, 'updateSchema'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update module table schema
     *
     * @param Event $e
     *
     * @return bool
     */
    public function updateSchema(Event $e)
    {
        $moduleVersion = $e->getParam('version');
        $updator = new Schema\Updator110($this);
        $result = $updator->upgrade($moduleVersion);

        return $result;
    }
}
