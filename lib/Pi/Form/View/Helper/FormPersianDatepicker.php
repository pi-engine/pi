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
use Pi\Form\Element\PersianDatepicker as PersianDatepicker;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\FormInput;

/**
 * PersianDatepicker element helper
 *
 * {@inheritDoc}
 * @see    https://babakhani.github.io/PersianWebToolkit
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormPersianDatepicker extends FormInput
{
    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, array $options = [])
    {
        if (!$element instanceof PersianDatepicker) {
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

        // Set calendar by language
        if (!isset($options['calendarType'])) {
            if ($language != 'fa') {
                $options['calendarType'] = 'gregorian';
            }
        }

        // Register script loading
        $view = $this->getView();

        // quick fix to prevent multi-css load : need to improve ZF class usage
        if (!$view->persian_datepicker_initialized) {
            $view->jquery(
                [
                    'extension/persian-datepicker.css',
                    'extension/persian-datepicker.js',
                    'extension/persian-date.js',
                ]
            );
        }
        $view->persian_datepicker_initialized = true;
        // end fix

        $format = !empty($options['format']) ? $options['format'] : 'YYYY/MM/DD';
        $element->setAttribute('data-date-format', $format);

        $class = $element->getAttribute('class');
        $class = ($class ? $class . ' ' : '') . 'persianDatepicker';
        $element->setAttribute('class', $class);
        $html = parent::render($element);

        $dpOptions = [];
        foreach ($options as $key => $val) {
            $key = lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key))));
            if (is_array($val)) {
                $dpOptions[] = $key . ': ' . json_encode($val);
            } elseif (is_numeric($val)) {
                $dpOptions[] = $key . ': ' . intval($val) . '';
            } else {
                $dpOptions[] = $key . ': "' . $val . '"';
            }
        }
        $datapickerOptions = implode(',' . PHP_EOL, $dpOptions);

        $id   = $element->getAttribute('id');
        $id   = $id ? '#' . $id : '.persianDatepicker';
        $html .= PHP_EOL . <<<EOT
<script>
    $(document).ready(function () {
        $("{$id}").pDatepicker({
            {$datapickerOptions}
        });
    });
</script>
EOT;

        return $html;
    }
}
