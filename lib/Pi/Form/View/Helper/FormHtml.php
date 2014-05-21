<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

//use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

/**
 * HTML render helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormHtml extends AbstractHelper
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, $options = array())
    {
        return $element->getValue();
    }
}
