<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Message\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Filter of reply message
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class ReplyFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add(array(
            'name'          => 'content',
            'filters'       => array(
                array(
                    'name'  => 'StringTrim',
                ),
            ),
        ));
    }
}
