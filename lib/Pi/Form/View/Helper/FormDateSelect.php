<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use IntlDateFormatter;
use Zend\Form\View\Helper\FormDateSelect as ZendFormElement;
use Zend\Form\ElementInterface;

/**
 * Form element helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormDateSelect extends ZendFormElement
{
    /**
     * Constructor
     */
    public function __construct()
    {
        if (extension_loaded('intl')) {
            parent::__construct();

            return;
        }

        $this->dateType = 'Y-m-d';
        $this->pattern = '';
    }

    /**
     * Invoke helper as function
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface $element
     * @param  string|int|null  $dateType
     * @param  null|string      $locale
     * @return FormDateSelect
     */
    public function __invoke(ElementInterface $element = null, $dateType = null, $locale = null)
    {
        if (extension_loaded('intl')) {
            if (null === $dateType) {
                $dateType = IntlDateFormatter::LONG;
            }

            return parent::__invoke($element, $dateType, $locale);
        }

        $this->setDateType($dateType);

        if ($locale !== null) {
            $this->setLocale($locale);
        }

        return $this->render($element);
    }
    /**
     * Parse the pattern
     *
     * @param  bool $renderDelimiters
     * @return array
     */
    protected function parsePattern($renderDelimiters = true)
    {
        if (extension_loaded('intl')) {
            return parent::parsePattern($renderDelimiters);
        }

        $result = array(
            'year'  => 'year',
            'month' => 'month',
            'day'   => 'day',
        );

        return $result;
    }

    /**
     * Set date formatter
     *
     * @param  int $dateType
     * @return FormDateSelect
     */
    public function setDateType($dateType)
    {
        if (extension_loaded('intl')) {
            return parent::setDateType($dateType);
        }

        $this->dateType = $dateType;

        return $this;
    }

    /**
     * Create a key => value options for months
     *
     * @param string $pattern Pattern to use for months
     * @return array
     */
    protected function getMonthsOptions($pattern)
    {
        if (extension_loaded('intl')) {
            return parent::getMonthsOptions($pattern);
        }

        $result = array();
        for ($month = 1; $month <= 12; $month++) {
            $result[$month] = str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        return $result;
    }

    /**
     * Create a key => value options for days
     *
     * @param  string $pattern Pattern to use for days
     * @return array
     */
    protected function getDaysOptions($pattern)
    {
        if (extension_loaded('intl')) {
            return parent::getDaysOptions($pattern);
        }

        $result = array();
        for ($day = 1; $day <= 31; $day++) {
            $result[$day] = str_pad($day, 2, '0', STR_PAD_LEFT);
        }

        return $result;
    }

}
