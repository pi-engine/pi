<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Pi Engine sevice abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractService
{
    /** @var string Identifier for file name of config data */
    protected $fileIdentifier = '';

    /** @var array Options */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array $options Parameters to send to the service
     */
    public function __construct($options = array())
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
     * @return void
     */
    public function setOptions($options = array())
    {
        if (is_string($options)) {
            $options = Pi::config()->load($options) ?: array();
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
     * @param mixed $value
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
     * @param string $name
     *
     * @return mixed|null
     */
    public function getOption($name)
    {
        $result = isset($this->options[$name]) ? $this->options[$name] : null;

        return $result;
    }
}
