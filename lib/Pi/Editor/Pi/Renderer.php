<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Editor\Pi;

use Pi\Editor\AbstractRenderer;
use Zend\Form\ElementInterface;

/**
 * Default editor renderer
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Renderer extends AbstractRenderer
{
    /**
     * Renders editor contents
     *
     * @param  ElementInterface $element
     * @return string
     */
    public function render(ElementInterface $element)
    {
        return $this->view->formTextarea($element);
    }
}
