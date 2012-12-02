<?php
/**
 * Pi Engine sevice abstract class
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
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;
use Pi;

abstract class AbstractService
{
    /**
     * Identifier for file name of config data
     * @var string
     */
    protected $fileIdentifier = '';

    /**
     * options
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param array     $options    Parameters to send to the service during instanciation
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Loads options
     *
     * Options data will be loaded from config file if defined
     *
     * @param array    $options
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

    public function getOptions()
    {
        return $this->options;
    }
}
