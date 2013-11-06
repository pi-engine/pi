<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Custom\User\Form\View\Helper;

use Zend\Form\ElementInterface;
//use Zend\Form\Exception;
use Zend\Form\View\Helper\AbstractHelper;
use Pi;

/**
 * Editor element helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Interest extends AbstractHelper
{
    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|self
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render editor
     *
     * @param  ElementInterface $element
     *
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $html = <<<EOD
        <input type="checkbox" value='interest1' name="interest[]">interest1
        <input type="checkbox" value='interest2' name="interest[]">interest2
        <input type="checkbox" value='interest3' name="interest[]">interest3
        <input type="checkbox" value='interest4' name="interest[]">interest4
        <input type="checkbox" value='interest5' name="interest[]">interest5
        <input type="checkbox" value='interest5' name="interest[]">interest6

EOD;

        return $html;
    }
}
