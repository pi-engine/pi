<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Filter and validator of category merge form
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class CategoryMergeFilter extends InputFilter
{
    /**
     * Initializing validator and filter 
     */
    public function __construct()
    {
        $this->add(array(
            'name'     => 'from',
            'required' => true,
        ));

        $this->add(array(
            'name'     => 'to',
            'required' => true,
        ));
    }
}
