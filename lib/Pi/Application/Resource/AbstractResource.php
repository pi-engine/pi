<?php
/**
 * Bootstrap resource interface
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
 * @subpackage      Resource
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Resource;

use Pi\Application\Engine\AbstractEngine;

abstract class AbstractResource
{
    protected $options = array();
    protected $engine;
    protected $application;

    public function __construct(AbstractEngine $engine, $options = array())
    {
        $this->options = $options;
        $this->engine = $engine;
        $this->application = $engine->application();
    }

    public function boot()
    {
        return $this;
    }
}
