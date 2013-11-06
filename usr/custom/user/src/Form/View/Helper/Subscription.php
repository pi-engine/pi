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
class Subscription extends AbstractHelper
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
        <input type="checkbox" value='subscription1' name="subscription[]">Subscription1
        <input type="checkbox" value='subscription2' name="subscription[]">Subscription2
        <input type="checkbox" value='subscription3' name="subscription[]">Subscription3
        <input type="checkbox" value='subscription4' name="subscription[]">Subscription4
        <input type="checkbox" value='subscription5' name="subscription[]">Subscription5
        <input type="checkbox" value='subscription6' name="subscription[]">Subscription6
EOD;

        return $html;
    }
}
