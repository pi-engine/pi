<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            'name'      => 'cache_ttl',
            'required'  => false,
        ));

        $this->add(array(
            'name'      => 'cache_level',
            'required'  => false,
        ));
    }
}
