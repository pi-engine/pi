<?php
/**
 * Standard application engine class
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
 * @package         Application
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Engine;

use Pi;
use Pi\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

/**
 * Pi standard application engine
 *
 * Tasks: load configs, default listeners, module manager, bootstrap, application; boot application; run application
 *
 * @author      Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Standard extends AbstractEngine
{
    const SECTION = self::FRONT;

    /**
     * Identifier for file name of option data
     * @var string
     */
    protected $fileIdentifier = 'front';

    /**
     * @var array
     */
    protected $resources = array(
        'options'   => array(),
        'instances' => array()
    );

    /**
     * Run the application
     *
     * @return boolean
     */
    public function run()
    {
        $status = $this->bootstrap();
        if (false === $status) {
            return false;
        }

        $response = $this->application->getResponse();
        $response->getHeaders()->addHeaders(array(
            'content-type'      => sprintf('text/html; charset=%s', Pi::config('charset')),
            'content-language'  => Pi::config('locale'),
        ));

        $this->application->run();

        return true;
    }

    /**
     * Bootstrap
     */
    public function bootstrap()
    {
        // Load Pi services
        $status = $this->loadService();
        if (false === $status) {
            return false;
        }

        // Load application, which could be called during resouce setup
        $application = $this->application();


        // Load application resources
        $status = $this->setupResource();
        if (false === $status) {
            return false;
        }

        // Boot application
        $application->bootstrap();

        return $this;
    }

    /**
     * Load application
     */
    public function application()
    {
        if (!$this->application) {
            // setup service manager
            $serviceManager = new ServiceManager(new ServiceManagerConfig($this->options['service_manager']));
            if (isset($this->options['application'])) {
                $serviceManager->get('Configuration')->exchangeArray($this->options['application']);
            }
            $this->application = $serviceManager->get('Application');
            $this->application->setEngine($this)->setSection($this->section());
        }

        return $this->application;
    }

    /**
     * Load Pi application services
     *
     * @return boolean
     */
    protected function loadService()
    {
        if (!empty($this->options['service'])) {
            foreach ($this->options['service'] as $service => $options) {
                try {
                    Pi::service($service, $options);
                } catch (\Exception $e) {
                    trigger_error(sprintf('Service "%s" failed: %s', $service, $e->getMessage()), E_USER_ERROR);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Sets up resources
     *
     * @param  array resources
     * @return boolean
     */
    protected function setupResource()
    {
        $this->resources['options'] = $this->options['resource'];

        foreach (array_keys($this->resources['options']) as $resource) {
            $result = $this->loadResource($resource);
            if (false === $result) {
                trigger_error(sprintf('Resource "%s" failed', $resource), E_USER_ERROR);
                return false;
                //throw new \Exception(sprintf('Process terminated in resource "%s"', $resource));
            }
        }
        return true;
    }

    /**
     * Loads a resource
     *
     * @param string $resource
     * @param array $options    custom options, will be merged with native options
     * @return void
     */
    public function loadResource($resource, $options = array())
    {
        if (!isset($this->resources['instances'][$resource])) {
            // Skip resource if disabled
            if (isset($this->resources['options'][$resource]) && false === $this->resources['options'][$resource]) {
                $this->resources['instances'][$resource] = true;
            // Load resource with native and custom options
            } else {
                if (!empty($this->resources['options'][$resource])) {
                    if (is_string($this->resources['options'][$resource])) {
                        $opt = Pi::config()->load(sprintf('resource.%s.php', $this->resources['options'][$resource]));
                    } else {
                        $opt = $this->resources['options'][$resource];
                    }
                    if (!empty($opt) && is_array($opt)) {
                        $options = array_merge($opt, $options);
                    }
                }
                $class = sprintf('Pi\\Application\\Resource\\%s', ucfirst($resource));
                $resourceInstance = new $class($this, $options);

                $result = $resourceInstance->boot();
                $this->resources['instances'][$resource] = (null === $result) ? true : $result;
            }
        }
        return $this->resources['instances'][$resource];
    }
}
