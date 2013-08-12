<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer;

use Pi;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;

/**
 * Theme maintenance
 *
 * @see Pi\Application\Service\Asset for asset maintenance
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Theme
{
    /** @var array Operation result */
    protected $result;

    /** @var array Options */
    protected $options;

    /** @var EventManager Installer event manager */
    protected $events;

    /** @var Event Installer event */
    protected $event;

    /**
     * Files requird by theme:
     *
     * - front: files required by front layout
     * - admin: files required by admin layout
     *
     * @var array
     */
    protected $fileList = array(
        'front' => array(
            // Complete layout template:
            // header, footer, body, blocks, navigation
            'template/layout-front.phtml',
            // Simple page layout: header, footer, body
            'template/layout-simple.phtml',
            // Content with stylesheets
            'template/layout-style.phtml',
            // Raw content
            'template/layout-content.phtml',

            // Pagination template
            'template/paginator.phtml',

            // Exception error template
            'template/error.phtml',
            // 404 error template
            'template/error-404.phtml',
            // Denied error template
            'template/error-denied.phtml',

            // Main css file
            'asset/css/style.css',
        ),
        'admin' => array(
            // Backoffice layout
            'template/layout-admin.phtml',

            // Pagination template
            'template/paginator.phtml',

            // Exception error template
            'template/error.phtml',
            // 404 error template
            'template/error-404.phtml',
            // Denied error template
            'template/error-denied.phtml',

            // Main css file
            'asset/css/style.css',
        ),
    );

    /**
     * Magic methods for install, uninstall, update, etc.
     *
     * @param string $method
     * @param array $args
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function __call($method, $args)
    {
        if (!in_array($method, array('install', 'uninstall', 'update'))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid action "%s".', $method)
            );
        }

        $name = array_shift($args);
        $options = empty($args) ? array() : array_shift($args);
        $version = isset($options['version']) ? $options['version'] : null;
        $event = new Event;
        $event->setParams(array(
            'name'          => $name,
            'version'       => $version,
            'action'        => $method,
            'config'        => array(),
            //'result'        => array(),
        ));
        $this->event = $event;
        $this->attachDefaultListeners();

        $this->getEventManager()->trigger('start', null, $event);

        // Define callback used to determine whether or not to short-circuit
        $shortCircuit = function ($r) {
            if (false === $r) {
                return true;
            }
            return false;
        };
        $result = $this->getEventManager()->trigger(
            sprintf('%s.pre', $method),
            null,
            $event,
            $shortCircuit
        );
        if ($result->stopped()) {
            return false;
        }
        $actionMethod = $method . 'Action';
        $result = $this->{$actionMethod}();
        if (!$result['status']) {
            $ret = array($method => $result);
            $this->event->setParam('result', $ret);
            return false;
        }
        $result = $this->getEventManager()->trigger(
            'process',
            null,
            $event,
            $shortCircuit
        );
        if ($result->stopped()) {
            return false;
        }

        $this->getEventManager()->trigger(
            sprintf('%s.post', $method),
            null,
            $event
        );
        $this->getEventManager()->trigger('finish', null, $event);

        $status = true;
        $result = $event->getParam('result');
        //foreach ($result as $action => $state) {
            if ($result['status'] === false) {
                $status = false;
                //break;
            }
        //}

        return $status;
    }

    /**
     * Get EventManager
     *
     * @return EventManager
     */
    public function getEventManager()
    {
        if (!$this->events) {
            $this->events = new EventManager;
        }

        return $this->events;
    }

    /**
     * Attach default listeners
     *
     * @return void
     */
    protected function attachDefaultListeners()
    {
        $events = $this->getEventManager();
        $events->attach('start', array($this, 'loadConfig'));
        $events->attach('finish', array($this, 'clearCache'));
    }

    /**
     * Clear registry caches
     *
     * @param Event $e
     * @return void
     */
    public function clearCache(Event $e)
    {
        Pi::registry('theme')->flush();
        Pi::registry('themelist')->flush();
    }

    /**
     * Get result
     *
     * @see Module\getResult()
     * @return array
     */
    public function getResult()
    {
        return $this->event->getParam('result');
    }

    /**
     * Render result messages
     *
     * @param array|null $message
     * @return string
     */
    public function renderMessage($message = null)
    {
        if (null === $message) {
            $message = (array) $this->getResult();
        }
        $content = '';
        foreach ($message as $action => $state) {
            $content .= '<p>';
            $content .= $action . ': '
                      . (($state['status'] === false)
                         ? 'failed' : 'passed'
                      );
            if (!empty($state['message'])) {
                $content .= '<br />&nbsp;&nbsp;' . implode(
                    '<br />&nbsp;&nbsp;',
                    (array) $state['message']
                );
            }
            $content .= '</p>';
        }

        return $content;
    }

    /**
     * Load theme meta
     * @param Event $e
     * @return void
     */
    public function loadConfig(Event $e)
    {
        $config = Pi::service('theme')->loadConfig($e->getParam('name'));
        $e->setParam('config', $config);
    }

    /**
     * Canonize theme meta
     *
     * @param array $data
     * @return array
     */
    protected function canonizeData(array $data)
    {
        $return = array(
            'name'      => isset($data['name'])
                            ? $data['name'] : $this->event->getParam('name'),
            'version'   => $data['version'],
            'update'    => isset($data['update']) ? $data['update'] : time(),
            'type'      => !empty($data['type']) ? $data['type'] : 'both',
        );

        return $return;
    }

    /**
     * Install action
     *
     * @return array
     */
    protected function installAction()
    {
        $name = $this->event->getParam('name');
        $config = $this->event->getParam('config');
        $type = isset($config['type']) ? $config['type'] : 'both';
        $result = array(
            'status'    => true,
            'message'   => ''
        );
        if (empty($config['parent'])
            && $files = $this->checkFiles($name, $type)
        ) {
            $result = array(
                'status'    => false,
                'message'   => 'Files missing: ' . implode(' ', $files)
            );
        } else {
            $data = $this->canonizeData($config);
            $model = Pi::model('theme');
            $row = $model->createRow($data);
            $row->save();
            if (!$row->id) {
                $result = array(
                    'status'    => false,
                    'message'   => 'Theme row save failed.'
                );
                return $result;
            }
            $status = Pi::service('asset')->publish('theme/' . $name);
            if (!$status) {
                $result = array(
                    'status'    => false,
                    'message'   => 'Theme asset publish failed.'
                );
            } else {
                Pi::service('asset')->publishCustom($name);
            }
        }

        return $result;
    }

    /**
     * Update action
     *
     * @return array
     */
    protected function updateAction()
    {
        $name = $this->event->getParam('name');
        $version = $this->event->getParam('version');
        $config = $this->event->getParam('config');
        $status = true;
        $message = '';

        $row = Pi::model('theme')->find($name, 'name');
        $isUpgrade = version_compare($version, $config['version'], '<');
        if (!$isUpgrade) {
            $row->update = time();
            $row->save();
            $message = __('Theme updated successfully.');
        } else {
            $data = $this->canonizeData($config);
            $row->assign($data);
            $row->save();
            if (!$row->id) {
                $status = false;
                $message = __('Theme failed to update.');
            } else {
                $message = __('Theme upgraded successfully.');
                $res = Pi::service('asset')->publish('theme/' . $name);
                if (!$res) {
                    $status = false;
                    $message = __('Theme asset publish failed.');
                }
            }
        }
        Pi::service('asset')->removeCustom($name);
        Pi::service('asset')->publishCustom($name);

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * Uninstall action
     *
     * @return array
     */
    protected function uninstallAction()
    {
        $name = $this->event->getParam('name');
        if ('default' == $name) {
            throw new \Exception('Theme "default" is protected.');
        }
        $result = array(
            'status'    => true,
            'message'   => ''
        );
        $model = Pi::model('theme');
        $status = $model->delete(array('name' => $name));
        if (!$status) {
            $result = array(
                'status'    => false,
                'message'   => 'Theme uninstallation failed.'
            );
            return $result;
        }

        $status = Pi::service('asset')->remove('theme/' . $name);
        if (!$status) {
            $result = array(
                'status'    => false,
                'message'   => 'Theme assets removal failed.'
            );
        }
        Pi::service('asset')->removeCustom($name);

        return $result;
    }

    /**
     * Check if required files available
     *
     * @param string $theme
     * @param string $type
     * @return array
     * @throws \Exception
     */
    protected function checkFiles($theme, $type = 'both')
    {
        $fileList = $this->fileList;
        $path = Pi::path('theme/' . $theme);

        if (isset($fileList[$type])) {
            $files = $fileList[$type];
        } elseif ('both' == $type) {
            $files = array_unique(
                array_merge($fileList['front'], $fileList['admin'])
            );
        } else {
            throw new \Exception(
                sprintf('Wrong type "%s" configured.', $type)
            );
        }
        $missingFiles = array();
        foreach ($files as $file) {
            if (!file_exists($path . '/' . $file)) {
                $missingFiles[] = $file;
            }
        }

        return $missingFiles;
    }
}
