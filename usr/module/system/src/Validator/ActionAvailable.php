<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Validator;

use Laminas\Validator\AbstractValidator;

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
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = [
            static::ACTION_UNAVAILABLE => __('The action is not available.'),
        ];
        parent::__construct($options);
    }

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

        $module     = $context['module'];
        $controller = $context['controller'];
        $action     = $value;

        $controllerClass = sprintf(
            'Module\\%s\Controller\Front\\%sController',
            ucfirst($module),
            ucfirst($controller)
        );
        $actionMethod    = $action . 'Action';
        if (!method_exists($controllerClass, $actionMethod)) {
            $this->error(static::ACTION_UNAVAILABLE);
            return false;
        }

        return true;
    }
}
