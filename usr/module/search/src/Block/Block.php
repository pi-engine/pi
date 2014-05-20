<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */
namespace Module\Search\Block;

use Pi;

class Block
{
    public static function search($options)
    {
        $formAction = Pi::service('url')->assemble('search');

        $list = array();
        $module = Pi::service('module')->current();
        $modules = Pi::registry('search')->read();
        if ($module && isset($modules[$module])) {
            $list = array(
               ''       => _b('Global'),
               $module  => _b('Current module'),
            );
        }

        return array(
            'options'   => $options,
            'action'    => $formAction,
            'list'      => $list
        );
    }
}