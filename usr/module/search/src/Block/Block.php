<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */
namespace Module\Search\Block;

use Pi;

class Block
{
    public static function search()
    {
        $formAction = Pi::service('url')->assemble('search');

        $options = array();
        $module = Pi::service('module')->current();
        if ($module && 'system' != $module) {
            $options = array(
               ''       => _b('Global'),
               $module  => _b('Current module'),
            );
        }

        return array(
            'options'   => $options,
            'action'    => $formAction,
        );
    }
}