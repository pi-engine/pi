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
class Time extends AbstractHelper
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
        <script src="%s"></script>
        <div id="js-time-element" data-value="%s" class="form-inline">
        </div>
        <script>
            new EEFOCUS_DATA.Time("js-time-element", %s, ['start', 'end']);
        </script>
EOD;
        return sprintf($html, 
            Pi::url('static/js/eefocus-time.js'),
            $element->getValue(),
            json_encode(array(
                'year'  => date('Y'),
                'month' => date('n')
            )) 
        );
    }
}
