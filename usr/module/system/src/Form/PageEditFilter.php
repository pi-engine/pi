<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Page edit form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageEditFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add(array(
            'name'      => 'id',
            'required'  => true,
        ));

        $this->add(array(
            'name'      => 'cache_type',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'cache_ttl',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'cache_level',
            'required'  => false,
        ));
    }
}
