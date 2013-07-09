<?php
/**
 * Pi module installer resource
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
 * @package         Pi\Application
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Pi\Application\Installer\Resource;

use Pi;
use Zend\EventManager\Event;

class AbstractResource
{
    //protected $action;
    protected $event;
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * Determine whether to skip upgrade for current resource
     *
     * Performe upgrade in anyway if system is in development mode; Skip upgrade if module version is already greater than configuration
     *
     * @return bool
     */
    protected function skipUpgrade()
    {
        return (Pi::environment() == 'development' || !$this->versionCompare()) ? false : true;
    }

    /**
     * Check if module version is greater than configuration version
     *
     * @param string $operator
     * @return boolean
     */
    protected function versionCompare($operator = '>=')
    {
        $config = $this->event->getParam('config');
        $configVersion = $config['meta']['version'];
        $moduleVersion = $this->event->getParam('version');
        if (version_compare($moduleVersion, $configVersion, $operator)) {
            return true;
        }
        return false;
    }

    public function fooAction()
    {
        // Return full result with status and message
        return array(
            'status'    => true,
            'message'   => 'Just for test'
        );
        // return status
        return false;
        // return void if no action performed
        return;
    }
}
