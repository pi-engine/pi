<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Field;

use Pi;

/**
 * Subject element handler
 *
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Subject extends CommonHandler
{
    /**
     * {@inheritDoc}
     */
    public function resolve($value, $options = array())
    {
        $markup = isset($options['markup']) ? $options['markup'] : '';
        if ($markup) {
            $result = Pi::service('markup')->render($value, 'html', $markup);
        } else {
            $result = Pi::service('markup')->render($value, 'html');
        }
        
        return $result;
    }
}
