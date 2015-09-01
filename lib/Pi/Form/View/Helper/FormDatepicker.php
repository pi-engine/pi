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
    public function render(ElementInterface $element, array $options = array())
    {
        if (!$element instanceof Datepicker) {
            throw new Exception\InvalidElementException('Invalid element type');
        }

        // Canonize options
        $options    = array_replace((array) $element->getOption('datepicker'), $options);
        $language   = !empty($options['language']) ? $options['language'] : Pi::service('i18n')->getLocale();
        $segs       = explode(' ', str_replace(array('-', '_'), ' ', $language));
        $language   = array_shift($segs);
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
        $bsLoad = array(
            'datepicker/datepicker.css',
            'datepicker/bootstrap-datepicker.js'
        );
        if (!empty($options['language'])) {
            $bsLoad[] = sprintf('datepicker/locales/bootstrap-datepicker.%s.js', $options['language']);
        }
        $view->bootstrap($bsLoad, array(), null, false);

        $format = !empty($options['format']) ? $options['format'] : 'mm/dd/yyy';
        $element->setAttribute('data-date-format', $format);

        $class = $element->getAttribute('class');
        $class = ($class ? $class . ' ' : '') . 'datepicker';
        $element->setAttribute('class', $class);
        $html = parent::render($element);

        $dpOptions = array();
        foreach ($options as $key => $val) {
            $key = lcfirst(str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $key))));
            $dpOptions[] = $key . ': "' . $val . '"';
        }
        $datapickerOptions = implode(',' . PHP_EOL, $dpOptions);

        $id = $element->getAttribute('id');
        $id = $id ? '#' . $id : '.datepicker';
        $html .= PHP_EOL . <<<EOT
<script>
    $("{$id}").datepicker({
        {$datapickerOptions}
    })
</script>
EOT;

        return $html;
    }
}
