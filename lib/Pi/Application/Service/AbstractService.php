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

    /** @var array */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array $options Parameters to send to the service during instanciation
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * Options data will be loaded from config file if defined
     *
     * @param array $options
     * @return void
     */
    public function setOptions($options = array())
    {
        if ($this->fileIdentifier && empty($options)) {
            $options = Pi::config()->load('service.' . $this->fileIdentifier . '.php');
            /*
            if ($options) {
                $options = array_merge($opt, $options);
            } else {
                $options = $opt;
            }
            */
        }

        $this->options = array_merge($this->options, $options);
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
}
