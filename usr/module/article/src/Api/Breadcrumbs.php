<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $module = $this->module;
        $moduleData = Pi::registry('module')->read($module);
        //$route = Pi::api('api', $this->module)->getRouteName();
        $route = 'article';
        $home   = Pi::service('module')->config('default_homepage', $module);
        $home   = $home ? Pi::url($home) : Pi::service('url')->assemble(
            'default',
            array('module' => $module)
        );
        $result = array(
            array(
                'label' => $moduleData['title'],
                'href'  => $home,
            ),
        );
        
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
            $result[] = array(
                'label' => $category->title,
                'href'  => Pi::service('url')->assemble($route, array(
                    'module'     => $module,
                    'controller' => 'list',
                    //'action'     => 'all',
                    'category'   => $category->slug ?: $category->id,
                )),
            );
            $result[] = array(
                'label' => __('Content'),
            );
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
            $result[] = array(
                'label' => $title,
            );
        } else if ('topic' == $params['controller']
            && 'all-topic' == $params['action']
        ) {
            $result[] = array(
                'label' => __('Topic'),
            );
        } else if ('topic' == $params['controller']
            && ('index' == $params['action'] || 'list' == $params['action'])
        ) {
            $result[] = array(
                'label' => __('Topic'),
                'href'  => Pi::service('url')->assemble('default', array(
                    'module'     => $module,
                    'controller' => 'topic',
                )),
            );
            if ('index' == $params['action']) {
                $result[] = array(
                    'label' => $params['topic'],
                );
            } elseif ('list' == $params['action']) {
                $result[] = array(
                    'label' => $params['topic'],
                    'href'  => Pi::service('url')->assemble($route, array(
                        'module'     => $module,
                        'topic'      => $params['topic'],
                    )),
                );
                $result[] = array(
                    'label' => __('All'),
                );
            }
        }
        
        return $result;
    }
}
