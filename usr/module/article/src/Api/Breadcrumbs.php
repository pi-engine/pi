<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractBreadcrumbs;

/**
 * Custom breadcrumbs class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Breadcrumbs extends AbstractBreadcrumbs
{
    /**
     * {@inheritDoc}
     */
    protected $module = 'article';

    /**
     * {@inheritDoc}
     */
    public function load()
    {
        $module     = $this->module;
        $moduleData = Pi::registry('module')->read($module);
        //$route = Pi::api('api', $this->module)->getRouteName();
        $route  = 'article';
        $home   = Pi::service('module')->config('default_homepage', $module);
        $home   = $home ? Pi::url($home) : Pi::service('url')->assemble(
            'default',
            ['module' => $module]
        );
        $result = [
            [
                'label' => $moduleData['title'],
                'href'  => $home,
            ],
        ];

        $params = Pi::service('url')->getRouteMatch()->getParams();

        if ('article' == $params['controller']
            && 'detail' == $params['action']
        ) {
            $model = Pi::model('article', $module);
            if (isset($params['slug']) && $params['slug']) {
                $row = Pi::model('extended', $module)->find($params['slug'], 'slug');
                $row = $model->find($row->article);
            } else {
                $row = $model->find($params['id']);
            }
            $category = Pi::model('category', $module)->find($row->category);
            $result[] = [
                'label' => $category->title,
                'href'  => Pi::service('url')->assemble($route, [
                    'module'     => $module,
                    'controller' => 'list',
                    //'action'     => 'all',
                    'category'   => $category->slug ?: $category->id,
                ]),
            ];
            $result[] = [
                'label' => __('Content'),
            ];
        } elseif ('list' == $params['controller']
            && 'all' == $params['action']
        ) {
            if ('all' == $params['category']) {
                $title = __('All');
            } else {
                $model = Pi::model('category', $module);
                if (is_numeric($params['category'])) {
                    $row = $model->find($params['category']);
                } else {
                    $row = $model->find($params['category'], 'slug');
                }
                $title = $row->title;
            }
            $result[] = [
                'label' => $title,
            ];
        } else if ('topic' == $params['controller']
            && 'all-topic' == $params['action']
        ) {
            $result[] = [
                'label' => __('Topic'),
            ];
        } else if ('topic' == $params['controller']
            && ('index' == $params['action'] || 'list' == $params['action'])
        ) {
            $result[] = [
                'label' => __('Topic'),
                'href'  => Pi::service('url')->assemble('default', [
                    'module'     => $module,
                    'controller' => 'topic',
                ]),
            ];
            if ('index' == $params['action']) {
                $result[] = [
                    'label' => $params['topic'],
                ];
            } elseif ('list' == $params['action']) {
                $result[] = [
                    'label' => $params['topic'],
                    'href'  => Pi::service('url')->assemble($route, [
                        'module' => $module,
                        'topic'  => $params['topic'],
                    ]),
                ];
                $result[] = [
                    'label' => __('All'),
                ];
            }
        }

        return $result;
    }
}
