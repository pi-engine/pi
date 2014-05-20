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
use Module\Article\Controller\Admin\SetupController as Config;

/**
 * Filter and valid class for custom draft form
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftCustomFilter extends InputFilter
{
    /**
     * Initialize validator and filter 
     */
    public function __construct($mode, $options = array())
    {
        $this->add(array(
            'name'     => 'mode',
            'required' => true,
            'filters'  => array(
                array(
                    'name' => 'StringTrim',
                ),
            ),
        ));
        
        if (Config::FORM_MODE_CUSTOM == $mode) {
            foreach ($options['needed'] as $element) {
                $this->add(array(
                    'name'       => $element,
                    'required'   => true,
                    'validators' => array(
                        array(
                            'name' => 'Module\Article\Validator\NotEmpty',
                        ),
                    ),
                ));
            }
        }
    }
}
