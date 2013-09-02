<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Zend\InputFilter\InputFilter;

/**
 * Profile edit form filter
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class ProfileEditFilter extends InputFilter
{
    public function __construct($filters)
    {
        foreach ($filters as $filter) {
            if ($filter) {
                $this->add($filter);
            }
        }
    }
}