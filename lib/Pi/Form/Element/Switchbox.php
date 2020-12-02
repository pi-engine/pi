<?php
/**
 * Laminas Framework (http://framework.Laminas.com/)
 *
 * @link      http://github.com/Laminasframework/zf2 for the canonical source repository
 * @copyright  Copyright (c) 2005-2015 Laminas Technologies USA Inc. (http://www.Laminas.com)
 * @license    http://framework.Laminas.com/license/new-bsd     New BSD License
 */

namespace Pi\Form\Element;

use Traversable;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element;


class Switchbox extends Checkbox
{
    protected $attributes
        = [
            'type' => 'switchbox',
        ];
}
