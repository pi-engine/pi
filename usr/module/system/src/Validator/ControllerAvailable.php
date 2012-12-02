<?php
/**
 * Page controller-action validator
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
 * @since           3.0
 * @package         Module\System
 * @subpackage      Validator
 * @version         $Id$
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

class ControllerAvailable extends AbstractValidator
{
    const CONTROLLER_UNAVAILABLE = 'controllerUnavailable';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::CONTROLLER_UNAVAILABLE    => 'The controller is not available.',
    );

    /**
     * Page validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $module = $context['module'];
        $controller = $value;

        $controllerClass = sprintf('Module\\%s\\Controller\\Front\\%sController', ucfirst($module), ucfirst($controller));
        if (!class_exists($controllerClass)) {
            $this->error(static::CONTROLLER_UNAVAILABLE);
            return false;
        }

        return true;
    }
}
