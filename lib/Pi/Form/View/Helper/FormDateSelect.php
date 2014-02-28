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

use Pi;
//use IntlDateFormatter;
use Zend\Form\View\Helper\FormDateSelect as ZendFormDateSelect;
use Zend\Form\ElementInterface;

/**
 * Form element helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FormDateSelect extends ZendFormDateSelect
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

        $this->dateType = Pi::config('date_format'); //'Y-m-d';
        $this->pattern = '';
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ElementInterface $element = null, $dateType = null, $locale = null)
    {
        /*
        if (extension_loaded('intl')) {
            if (null === $dateType) {
                $dateType = IntlDateFormatter::LONG;
            }

            return parent::__invoke($element, $dateType, $locale);
        }
        */

        if ($dateType) {
            $this->setDateType($dateType);
        }

        if ($locale !== null) {
            $this->setLocale($locale);
        }

        return $this->render($element);
    }

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $dateFormat = $element->getOption('date_format');
        if ($dateFormat) {
            $this->setDateType($dateFormat);
        }

        return parent::render($element);
    }

    /**
     * {@inheritDoc}
     */
    protected function parsePattern($renderDelimiters = true)
    {
        /*
        if (extension_loaded('intl')) {
            return parent::parsePattern($renderDelimiters);
        }
        */

        $patternMap = array(
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
        );
        preg_match_all('/(y+|m+|d+)/i', $this->dateType, $matches);
        if ($matches) {
            $result = array();
            foreach ($matches[1] as $pattern) {
                $result[$patternMap[strtolower($pattern[0])]] = $pattern;
            }
        } else {
            $result = array(
                'year'  => 'Y',
                'month' => 'm',
                'day'   => 'd',
            );
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function setDateType($dateType)
    {
        /*
        if (extension_loaded('intl')) {
            return parent::setDateType($dateType);
        }
        */

        $this->dateType = $dateType;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getYearsOptions($minYear, $maxYear)
    {
        $result = parent::getYearsOptions($minYear, $maxYear);
        $result = array('invalid' => __('Year')) + $result;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function getMonthsOptions($pattern)
    {
        /*
        if (extension_loaded('intl')) {
            return parent::getMonthsOptions($pattern);
        }
        */

        $result = array(
            'invalid' => __('Month'),
        );
        for ($month = 1; $month <= 12; $month++) {
            if ($pattern) {
                $time = mktime(0, 0, 0, $month, 1, 1970);
                $result[$month] = date($pattern, $time);
            } else {
                $result[$month] = str_pad($month, 2, '0', STR_PAD_LEFT);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDaysOptions($pattern)
    {
        /*
        if (extension_loaded('intl')) {
            return parent::getDaysOptions($pattern);
        }
        */

        $result = array(
            'invalid' => __('Day'),
        );
        for ($day = 1; $day <= 31; $day++) {
            if ($pattern) {
                $time = mktime(0, 0, 0, 1, $day, 1970);
                $result[$day] = date($pattern, $time);
            } else {
                $result[$day] = str_pad($day, 2, '0', STR_PAD_LEFT);
            }
        }

        return $result;
    }

}
