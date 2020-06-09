<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Laminas\Form\View\Helper\FormCheckbox as LaminasFormElement;
use Laminas\Form\ElementInterface;

/**
 * Radio element helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormSwitchbox extends LaminasFormElement
{
    public function render(ElementInterface $element)
    {
        $attributes            = $element->getAttributes();
        $attributes['id']      = $element->getName();
        $attributes['class']     .= ' custom-control-input';
        $element->setAttributes($attributes);

        $rendered =  parent::render($element);
        return '<div class="custom-control custom-switch">' . $rendered . '  <label class="custom-control-label" for="' . $attributes['name'] . '"></label></div>';
    }
}
