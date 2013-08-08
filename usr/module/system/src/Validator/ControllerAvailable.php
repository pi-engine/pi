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
 * Controller availability check
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ControllerAvailable extends AbstractValidator
{
    /** @var string */
    const CONTROLLER_UNAVAILABLE = 'controllerUnavailable';

    /**
     * Message templates
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
