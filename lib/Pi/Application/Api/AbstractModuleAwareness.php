<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application\Api;

/**
 * Abstract class for module aware classes
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractModuleAwareness
{
    /** @var string Module name**/
    protected $module;

    /**
     * Constructor
     *
     * @param string $module
     *
     * @throws \Exception
     */
    public function __construct($module = null)
    {
        if (null === $module) {
            throw new \Exception('$module parameter is required.');
        }
        $this->setModule($module);
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
     * Get module name of the class
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }
}
