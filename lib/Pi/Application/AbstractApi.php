<?php
/**
 * Pi Engine API abstraction class
 *
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 */

namespace Pi\Application;

/**
 * Abstract class for API classes
 */
abstract class AbstractApi
{
    /** @var string Module name**/
    protected $module;

    /**
     * Constructor
     *
     * @param string|null $module
     */
    public function __construct($module = null)
    {
        if ($module) {
            $this->module = $module;
        }
    }

    /**
     * Set module for the API class
     *
     * @param string $module
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Get moduel name of the class
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }
}
