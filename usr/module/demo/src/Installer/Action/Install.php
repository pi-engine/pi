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
use Pi\Application\Installer\Action\Install as BasicInstall;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.pre', array($this, 'preInstall'), 1000);
        $events->attach('install.post', array($this, 'postInstall'), 1);
        parent::attachDefaultListeners();
        return $this;
    }

    public function preInstall(Event $e)
    {
        $this->setResult('pre-install', array(
            'status'    => true,
            'message'   => sprintf('Called from %s', __METHOD__),
        ));
    }

    public function postInstall(Event $e)
    {
        $model = Pi::model($this->module . '/test');
        $data = array(
            'message'   => sprintf(__('The module is installed on %s'), date('Y-m-d H:i:s')),
        );
        $model->insert($data);

        $model = Pi::model($this->module . '/page');
        $flag = 0;
        for ($page = 1; $page <= 100; $page++) {
            $model->insert(array(
                'title' => sprintf('Page #%d', $page),
                'flag'  => $flag++ % 2,
            ));
        }

        $this->setResult('post-install', array(
            'status'    => true,
            'message'   => sprintf('Called from %s', __METHOD__),
        ));
    }
}
