<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Feature list:
 *
 *  1. Default with ID: url/$id
 *  2. Slug with/without ID: url/$id-$slug; url/$slug
 *  3. Category with slug: url/$category/$id-$slug
 *  4. Time with slug: url/2012/08/23/$id-$slug
 *  5. Compound with category, time and slug:
 *      url/$category/2012/08/23/$id-$slug
 */
class RouteController extends ActionController
{
    /**
     * Create route link list and assign to template
     *
     * @return RouteController
     */
    protected function loadRoutes()
    {
        // Route definitions
        $params = array(
            'id'            => time(),
            'slug'          => 'china-love-day',
            'category'      => 'summer',
            'time'          => time(),
            'module'        => $this->getModule(),
            'controller'    => 'route',
        );
        $id = $this->params('id');
        $slug = $this->params('slug');
        $category = $this->params('category');
        $time = $this->params('time');
        $module = $this->getModule();
        $module = $this->params('module');
        $controller = $this->params('controller');

        $routeDefs = array(
            'demo-id'   => array(
                'label'     => __('Default with ID'),
                'route'     => 'demo-slug',
                'params'    => array(
                    'action'        => 'id',
                    'id'            => $params['id'],
                ),
            ),
            'demo-slug' => array(
                'route'     => 'demo-slug',
                'label'     => __('Slug'),
                'params'    => array(
                    'action'        => 'slug',
                    'slug'          => $params['slug'],
                ),
            ),
            'demo-slug-id'  => array(
                'label'     => __('Slug & ID'),
                'route'     => 'demo-slug',
                'params'    => array(
                    'action'        => 'slug',
                    'id'            => $params['id'],
                    'slug'          => $params['slug'],
                ),
            ),
            'demo-category' => array(
                'label'     => __('Category'),
                'route'     => 'demo-category',
                'params'    => array(
                    'action'        => 'category',
                    'id'            => $params['id'],
                    'slug'          => $params['slug'],
                    'category'      => $params['category'],
                ),
            ),
            'demo-time' => array(
                'label'     => __('Time'),
                'route'     => 'demo-time',
                'params'    => array(
                    'action'        => 'time',
                    'id'            => $params['id'],
                    'slug'          => $params['slug'],
                    'time'          => $params['time'],
                ),
            ),
            'demo-compound'  => array(
                'label'     => __('Time and category'),
                'route'     => 'demo-compound',
                'params'    => array(
                    'action'        => 'compound',
                    'id'            => $params['id'],
                    'slug'          => $params['slug'],
                    'time'          => $params['time'],
                    'category'      => $params['category'],
                ),
            ),
        );

        $rowset = Pi::model('route')->select(array(
            'module'    => $this->getModule(),
            'custom'    => 1,
            'active'    => 1
        ));
        $routeList = array();
        foreach ($rowset as $row) {
            $routeList[$row->name] = $row->data;
        }

        // Build route list
        $routes = array();
        $routes['list'] = array(
            'label' => __('List'),
            'url'   => $this->url('default', array(
                'module'        => $this->getModule(),
                'controller'    => 'route',
                'action'        => 'index',
            )),
        );

        foreach ($routeDefs as $key => $def) {
            if (!isset($routeList[$def['route']])) {
                continue;
            }
            $routes[$key] = array(
                'label' => $def['label'],
                'url'   => $this->url($def['route'], $def['params']),
            );
        }

        $this->view()->assign('routes', $routes);

        return $this;
    }

    /**
     * Process content to template
     */
    protected function process()
    {
        // Assign raw URI
        $uri = $this->getRequest()->getRequestUri();
        $this->view()->assign('uri', $uri);

        // Assign all route params
        $params = $this->params()->fromRoute();
        $this->view()->assign('params', $params);

        // Specify template,
        // otherwise template will be set up as {controller}-{action}
        $this->view()->setTemplate('demo-route');

        // Assign route list to template
        $this->loadRoutes();
    }

    /**
     * List
     */
    public function indexAction()
    {
        $this->process();

        // Specify page head title
        $this->view()->headTitle()->prepend('Demo route');

        // Specify meta parameter
        $this->view()->headMeta()->prependName('generator', 'DEMO');
    }

    /**
     * ID
     */
    public function idAction()
    {
        $this->process();
    }

    /**
     * Slug
     */
    public function slugAction()
    {
        $this->process();
    }

    /**
     * Category
     */
    public function categoryAction()
    {
        $this->process();
    }

    /**
     * Time
     */
    public function timeAction()
    {
        $this->process();
    }

    /**
     * Time and category
     */
    public function compoundAction()
    {
        $this->process();
    }
}
