<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Abstract class for Pi Engine service provider interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractService
{
    /** @var string Identifier for file name of config data */
    protected $fileIdentifier = '';

    /** @var array Options */
    protected $options = [];

    /**
     * Constructor
     *
     * @param array $options Parameters to send to the service
     */
    public function __construct($options = [])
    {
        // Set specified options
        if ($options) {
            $this->setOptions($options);
        // Load default options from config file
        } elseif ($this->fileIdentifier) {
            $this->setOptions('service.' . $this->fileIdentifier . '.php');
        }
    }

    /**
     * Set options
     *
     * @param array|string $options Array of options or config file name
     *
     * @return void
     */
    public function setOptions($options = [])
    {
        if (is_string($options)) {
            $options = Pi::config()->load($options) ?: [];
        }
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set an option
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Get an option
     *
     * @return mixed|null
     */
    public function getOption()
    {
        $args   = func_get_args();
        $result = $this->options;
        foreach ($args as $name) {
            if (!is_array($result)) {
                $result = null;
                break;
            }
            if (isset($result[$name])) {
                $result = $result[$name];
            } else {
                $result = null;
                break;
            }
        }

        return $result;
    }
}
