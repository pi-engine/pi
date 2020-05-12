<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Laminas\InputFilter\InputFilter;

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
        $this->add([
            'name'     => 'from',
            'required' => true,
        ]);

        $this->add([
            'name'     => 'to',
            'required' => true,
        ]);
    }
}
