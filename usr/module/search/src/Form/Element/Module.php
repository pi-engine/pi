<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Search\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class Module extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
        	$modules = Pi::registry('search')->read();
        	$list = array();
        	$list['all'] = __('All modules');
        	foreach ($modules as $key => $value) {
        		$list[$key] = $key;
        	}
            $this->valueOptions = $list;
        }
        return $this->valueOptions;
    }
}