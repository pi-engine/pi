<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Custom\Demo\Api;

use Pi;
use Pi\Application\Api\AbstractBreadcrumbs;

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
                'href'  => Pi::service('url')->assemble('default', array(
                        'module' => $this->module,
                    )),
            ),
        );

        if ('page' == _get('controller')) {
            if ('view' == _get('action')) {
                $result[] = array(
                    'label' => __('Pages'),
                    'href'  => Pi::service('url')->assemble('default', array(
                            'module'        => $this->module,
                            'controller'    => 'page',
                        )),
                );
                $result[] = array(
                    'label' => __('Content'),
                );
            } else {
                $result[] = array(
                    'label' => __('Pages'),
                );
            }
        }
        return $result;
    }
}
