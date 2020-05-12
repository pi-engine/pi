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

use Laminas\Form\ElementInterface;

/**
 * HTML render helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormHtmlRaw extends AbstractHelper
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, $options = [])
    {
        return $element->getValue();
    }
}
