<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Filter;

use Pi;
use Zend\InputFilter\Input;
use Module\Article\Draft;

/**
 * Summary filter class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Summary extends Input
{
    /**
     * Segment several string of article content as summary if its value is empty
     * 
     * @return string
     */
    public function getValue()
    {
        $value = parent::getValue();
        if (empty($value)) {
            $module  = Pi::service('module')->current();
            $value = Draft::generateArticleSummary(
                _post('content'),
                Pi::config('max_summary_length', $module)
            );
        }
        
        return $value;
    }
}
