<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

namespace Pi\Form\Element;

use Traversable;
use Zend\Form\Element\Checkbox;
use Zend\Form\Element;


class Switchbox extends Checkbox
{
    protected $attributes
        = [
            'type' => 'switchbox',
        ];
}
