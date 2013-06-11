<?php
/**
 * Pi module installer action
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

namespace Module\Demo\Installer\Action;
use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'postUpdate'));
        parent::attachDefaultListeners();
        return $this;
    }

    public function postUpdate(Event $e)
    {
        $model = Pi::model($module = $e->getParam('directory') . '/test');
        $data = array(
            'message'   => sprintf(__('The module is updated on %s'), date('Y-m-d H:i:s')),
        );
        $model->insert($data);

        $this->setResult('post-update', array(
            'status'    => true,
            'message'   => sprintf('Called from %s', __METHOD__),
        ));
    }
}
