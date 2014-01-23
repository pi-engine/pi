<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Api;

use Pi;
use Pi\Application\AbstractBreadcrumbs;

class Breadcrumbs extends AbstractBreadcrumbs
{
    /**
     * {@inheritDoc}
     */
    protected $module = 'demo';

    /**
     * {@inheritDoc}
     */
    public function load()
    {
        $result = array(
            array(
                'label' => __('Demo breadcrumbs'),
                'href'  => Pi::url('www'),
            )
        );

        return $result;
    }
}
