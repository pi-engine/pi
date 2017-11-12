<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Validator;

use Pi;
use Zend\Validator\AbstractValidator;

/**
 * Controller availability check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ControllerAvailable extends AbstractValidator
{
    /** @var string */
    const CONTROLLER_UNAVAILABLE = 'controllerUnavailable';

    /**
     * {@inheritDoc}
     */
    public function __construct($options = null)
    {
        $this->messageTemplates = array(
            static::CONTROLLER_UNAVAILABLE => __('The controller is not available.'),
        );
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

        $module = $context['module'];
        $controller = $value;

        $controllerClass = sprintf(
            'Module\\%s\Controller\Front\\%sController',
            ucfirst($module),
            ucfirst($controller)
        );
        if (!class_exists($controllerClass)) {
            $this->error(static::CONTROLLER_UNAVAILABLE);
            return false;
        }

        return true;
    }
}
