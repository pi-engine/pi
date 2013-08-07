<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

/**
 * Abstract class for API classes
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
