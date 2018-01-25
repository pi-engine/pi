<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $result = [
            [
                'label' => __('Demo breadcrumbs'),
                'href'  => Pi::service('url')->assemble('default', [
                    'module' => $this->module,
                ]),
            ],
        ];

        if ('page' == _get('controller')) {
            if ('view' == _get('action')) {
                $result[] = [
                    'label' => __('Pages'),
                    'href'  => Pi::service('url')->assemble('default', [
                        'module'     => $this->module,
                        'controller' => 'page',
                    ]),
                ];
                $result[] = [
                    'label' => __('Content'),
                ];
            } else {
                $result[] = [
                    'label' => __('Pages'),
                ];
            }
        }
        return $result;
    }
}
