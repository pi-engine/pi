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
class EndTime extends AbstractHelper
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
        Pi::service('view')->getHelper('js')->load(Pi::url('static/js/eefocus-time.js'));
        $maxYear = date('Y');
        $id = uniqid();

        return sprintf('
        <div class="form-inline" id="%s"></div>
        <script>
        new eefocus.EndTime("%s", %s, "%s", "%s");
        </script>
        ',
            $id,
            $id,
            $maxYear,
            $element->getName(),
            $element->getValue()
        );
    }
}
