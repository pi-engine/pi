<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Engine;

use Pi;
use Pi\Mvc\Application;

/**
 * Pi standard application engine
 *
 * Tasks:
 *
 *  - load configs, default listeners, module manager, bootstrap, application;
 * - boot application;
 * - run application
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Standard extends AbstractEngine
{
    /** @var string Section name */
    const SECTION = self::FRONT;

    /**
     * Identifier for file name of option data
     * @var string
     */
    protected $fileIdentifier = 'front';

    /**
     * Resource container
     * @var array
     */
    protected $resources = array(
        'options'   => array(),
        'instances' => array()
    );

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $status = $this->bootstrap();
        if (false === $status) {
            return false;
        }

        $this->application->run();

        return true;
    }

    /**
     * Bootstrap application
     */
    public function bootstrap()
    {
        // Load Pi services
        $status = $this->bootServices();
        if (false === $status) {
            return false;
        }

        // Boot application resources
        $status = $this->bootResources();
        if (false === $status) {
            return false;
        }

        // Load application, which could be called during resouce setup
        $application = $this->application();

        // Boot application
        $application->bootstrap();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function application()
    {
        if (!$this->application) {
            $options = isset($this->options['application'])
                       ? $this->options['application'] : array();
            $this->application = Application::load($options);
            $this->application->setEngine($this)->setSection($this->section());
        }

        return $this->application;
    }

    /**
     * Bood Pi application services
     *
     * @return bool
     */
    protected function bootServices()
    {
        if (!empty($this->options['service'])) {
            foreach ($this->options['service'] as $service => $options) {
                try {
                    Pi::service($service, $options);
                } catch (\Exception $e) {
                    trigger_error(sprintf(
                        'Service "%s" failed: %s',
                        $service,
                        $e->getMessage()
                    ), E_USER_ERROR);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Boot resources
     *
     * @return bool
     */
    protected function bootResources()
    {
        $this->resources['options'] = $this->options['resource'];

        foreach (array_keys($this->resources['options']) as $resource) {
            $result = $this->bootResource($resource);
            if (false === $result) {
                trigger_error(
                    sprintf('Resource "%s" failed', $resource),
                    E_USER_ERROR
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Loads a resource
     *
     * @param string    $resource
     * @param array     $options  Custom options
     * @return void
     */
    public function bootResource($resource, $options = array())
    {
        if (!isset($this->resources['instances'][$resource])) {
            // Skip resource if disabled
            if (isset($this->resources['options'][$resource])
                && false === $this->resources['options'][$resource]
            ) {
                $this->resources['instances'][$resource] = true;
            // Load resource with native and custom options
            } else {
                if (!empty($this->resources['options'][$resource])) {
                    if (is_string($this->resources['options'][$resource])) {
                        $opt = Pi::config()->load(
                            sprintf('resource.%s.php',
                                $this->resources['options'][$resource])
                        );
                    } else {
                        $opt = $this->resources['options'][$resource];
                    }
                    if (!empty($opt) && is_array($opt)) {
                        $options = array_merge($opt, $options);
                    }
                }
                $class = sprintf(
                    'Pi\Application\Bootstrap\Resource\\%s',
                    ucfirst($resource)
                );
                $resourceInstance = new $class($this, $options);

                $result = $resourceInstance->boot();
                $this->resources['instances'][$resource] = (null === $result)
                    ? true : $result;
            }
        }

        return $this->resources['instances'][$resource];
    }
}
