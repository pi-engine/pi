<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Page controller-action validator
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ActionAvailable extends AbstractValidator
{
    /** @var string */
    const ACTION_UNAVAILABLE = 'actionUnavailable';

    /**
     * Message templates
     * @var array
     */
    protected $messageTemplates = array(
        self::ACTION_UNAVAILABLE => 'The action is not available.',
    );

    /**
     * Page validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        $module = $context['module'];
        $controller = $context['controller'];
        $action = $value;

        $controllerClass = sprintf(
            'Module\\%s\Controller\Front\\%sController',
            ucfirst($module),
            ucfirst($controller)
        );
        $actionMethod = $action . 'Action';
        if (!method_exists($controllerClass, $actionMethod)) {
            $this->error(static::ACTION_UNAVAILABLE);
            return false;
        }

        return true;
    }
}
