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

use Pi;
use Pi\Form\Element\Datepicker as Datepicker;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormInput;

/**
 * Datepicker element helper
 *
 * @see http://bootstrap-datepicker.readthedocs.org/en/release/
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormDatepicker extends FormInput
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, array $options = [])
    {
        if (!$element instanceof Datepicker) {
            throw new Exception\InvalidElementException('Invalid element type');
        }

        // Canonize options
        $options  = array_replace((array)$element->getOption('datepicker'), $options);
        $language = !empty($options['language']) ? $options['language'] : Pi::service('i18n')->getLocale();
        $segs     = explode(' ', str_replace(['-', '_'], ' ', $language));
        $language = array_shift($segs);
        if ($segs) {
            $language .= '-' . strtoupper(implode('-', $segs));
        }
        if ('en' == $language) {
            unset($options['language']);
        } else {
            $options['language'] = $language;
        }

        // Register script loading
        $view = $this->getView();
        $view->jquery();
        // quick fix to prevent multi-css load : need to improve ZF class usage
        if ($view->core_datepicker_initialized) {
            $bsLoad = [];
        } else {
            $bsLoad = array(
                'datepicker/bootstrap-datepicker.min.css',
                'datepicker/bootstrap-datepicker.min.js'
            );
        }
        $view->core_datepicker_initialized = true;
        // end fix 
        if (!empty($options['language'])) {
            $bsLoad[] = sprintf('datepicker/locales/bootstrap-datepicker.%s.min.js', $options['language']);
        }
        $view->bootstrap($bsLoad, [], null, false);

        $format = !empty($options['format']) ? $options['format'] : 'mm/dd/yyy';
        $element->setAttribute('data-date-format', $format);

        $class = $element->getAttribute('class');
        $class = ($class ? $class . ' ' : '') . 'datepicker';
        $element->setAttribute('class', $class);
        $html = parent::render($element);

        $dpOptions = [];
        foreach ($options as $key => $val) {
            $key         = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key))));
            $dpOptions[] = $key . ': "' . $val . '"';
        }
        $datapickerOptions = implode(',' . PHP_EOL, $dpOptions);

        $id   = $element->getAttribute('id');
        $id   = $id ? '#' . $id : '.datepicker';
        $html .= PHP_EOL . <<<EOT
<script>
    $(document).ready(function () {
        $("{$id}").datepicker({
            {$datapickerOptions}
        })
    });
</script>
EOT;

        return $html;
    }
}
