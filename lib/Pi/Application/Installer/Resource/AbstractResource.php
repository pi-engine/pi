<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;
use Zend\EventManager\Event;

/**
 * Pi module installer resource abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AbstractResource
{
    /** @var Event Installer event */
    protected $event;

    /** @var array Meta config data */
    protected $config;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Set Event
     *
     * @param Event $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Determine whether to skip upgrade for current resource
     *
     * Performe upgrade in anyway if system is in development mode;
     * Skip upgrade if module version is already greater than configuration
     *
     * @return bool
     */
    protected function skipUpgrade()
    {
        return (Pi::environment() == 'development' || !$this->versionCompare())
            ? false : true;
    }

    /**
     * Check if module version is greater than configuration version
     *
     * @param string $operator
     * @return bool
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

    /**
     * Install resource
     *
     * Returns result as null, bool, or a message array
     *
     * <code>
     *  array(
     *      'status'    => <true|false>,
     *      'message'   => <Message array>[],
     *  );
     * </code>
     *
     * @return null|bool|array
     */
    public function installAction()
    {
        return;
    }

    /**
     * Uninstall resource
     *
     * Returns result as null, bool, or a message array
     *
     * <code>
     *  array(
     *      'status'    => <true|false>,
     *      'message'   => <Message array>[],
     *  );
     * </code>
     *
     * @return null|bool|array
     */
    public function uninstallAction()
    {
        return;
    }

    /**
     * Activate resource
     *
     * Returns result as null, bool, or a message array
     *
     * <code>
     *  array(
     *      'status'    => <true|false>,
     *      'message'   => <Message array>[],
     *  );
     * </code>
     *
     * @return null|bool|array
     */
    public function activateAction()
    {
        return;
    }

    /**
     * Deactivate resource
     *
     * Returns result as null, bool, or a message array
     *
     * <code>
     *  array(
     *      'status'    => <true|false>,
     *      'message'   => <Message array>[],
     *  );
     * </code>
     *
     * @return null|bool|array
     */
    public function deactivateAction()
    {
        return;
    }

    /**
     * Get identifier of current module
     *
     * @return string
     */
    protected function getModule()
    {
        $module = $this->event ? $this->event->getParam('module') : null;
        
        return $module;
    }
}
