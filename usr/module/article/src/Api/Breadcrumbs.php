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
        $home   = Pi::config('default_homepage', $module);
        $home   = $home ? Pi::url($home) : Pi::api('api', $module)->getUrl('home');
        $result = array(
            array(
                'label' => $moduleData['title'],
                'href'  => $home,
            ),
        );
        
        // Get actural controller & action
        $params = Pi::service('url')->getRouteMatch()->getParams();
        $page   = Pi::api('page', $module)->get($params['action']);
        if ($page) {
            $params = array_merge($params, array(
                'controller' => $page['controller'],
                'action'     => $page['action'],
            ));
        }
        
        if ('article' == $params['controller']
            && 'index' == $params['action']
        ) {
            return array();
        } elseif ('article' == $params['controller']
            && 'detail' == $params['action']
        ) {
            $row      = Pi::model('article', $module)->find($params['id']);
            $category = Pi::api('category', $module)->get($row->category);
            $result[] = array(
                'label' => $category['title'],
                'href'  => Pi::api('api', $module)->getUrl('detail', array(
                    'category' => $category['slug'] ?: $category['id'],
                ), array(
                    'category' => $row->category,
                    'cluster'  => $row->cluster,
                )),
            );
            $result[] = array(
                'label' => __('Content'),
            );
        } elseif ('list' == $params['controller']
            && 'index' == $params['action']
        ) {
            if ('all' == $params['category']) {
                $title = __('All');
            } else {
                $categoryId = Pi::api('category', $module)->slugToId(
                    $params['category']
                );
                $category = Pi::api('category', $module)->get($categoryId);
                $title    = $category['title'];
            }
            $result[] = array('label' => $title);
        } else if ('topic' == $params['controller']
            && 'all-topic' == $params['action']
        ) {
            $result[] = array('label' => __('Topic'));
        } else if ('topic' == $params['controller']
            && ('index' == $params['action'] || 'list' == $params['action'])
        ) {
            $result[] = array(
                'label' => __('Topic'),
                'href'  => Pi::api('api', $module)->getUrl('topics'),
            );
            if ('index' == $params['action']) {
                $result[] = array('label' => $params['topic']);
            } elseif ('list' == $params['action']) {
                $result[] = array(
                    'label' => $params['topic'],
                    'href'  => Pi::api('api', $module)->getUrl(
                        'topic-list',
                        array('topic' => $params['topic'])
                    ),
                );
                $result[] = array(
                    'label' => __('All'),
                );
            }
        }
        
        return $result;
    }
}
