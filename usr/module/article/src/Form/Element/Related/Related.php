<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form\Element\Related;

use Pi;
use Zend\Form\Element\Hidden;

/**
 * Related field element class of related compound
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Related extends Hidden
{
    /**
     * Custom element attributes
     * 
     * @var array 
     */
    protected $attributes = array(
        'type'  => 'Module\Article\Form\View\Helper\Related\Related',
    );
    
    /**
     * Constructor, set element attributes
     * 
     * @param string $name
     * @param array  $options
     */
    public function __construct($name = null, $options = array())
    {
        $this->setAttribute('id', 'related');
        
        parent::__construct($name, $options);
    }
}
