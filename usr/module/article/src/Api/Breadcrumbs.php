<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        $route  = Pi::api('api', $module)->getRouteName();
        $result = array(
            array(
                'label' => $moduleData['title'],
                'href'  => Pi::service('url')->assemble('default', array(
                    'module' => $module,
                )),
            ),
        );
        
        $params = Pi::service('url')->getRouteMatch()->getParams();
        
        if ('article' == $params['controller']
            && 'detail' == $params['action']
        ) {
            $model = Pi::model('article', $module);
            if (isset($params['slug'])) {
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
                    'action'     => 'all',
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
        }
        
        return $result;
    }
}
