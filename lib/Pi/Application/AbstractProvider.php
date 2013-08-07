<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

use Zend\View\ViewModel;

/**
 * Pi Engine Taxonomy content provider API
 *
 * @todo    Move the class to a Taxonomy dedicated namespace
 * @author  Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractProvider extends AbstractApi
{
    /**
     * Get renderer
     *
     * @return \Zend\View\Renderer
     */
    protected function getRenderer()
    {
        $renderer = Pi::engine()->application()
            ->getServiceManager()->get('viewRenderer');

        return $renderer;
    }

    /**
     * Get ViewModel
     *
     * @param array|null $data
     * @param array|null $options
     * @return ViewModel
     */
    protected function getViewModel($data = null, $options = null)
    {
        $viewModel = new ViewModel($data, $options);

        return $viewModel;
    }

    /**
     * Check if taxonomy available
     *
     * @return bool
     */
    public function isActive()
    {
        return Pi::service('module')->isActive($this->module);
    }

    /**
     * Check if an entity exists
     *
     * @param int $id
     * @return bool
     */
    abstract public function hasEntity($id);

    /**
     * Get an entity
     *
     * @param int $id
     * @return mixed
     */
    abstract public function getEntity($id);

    /**
     * Get list of entities
     *
     * @param array         $ids
     * @param array|null    $fields
     * @return array
     */
    abstract public function getList($ids, $fields = null);

    /**
     * Render an entity
     *
     * @param int       $id
     * @param string    $template
     * @return string
     */
    abstract public function renderEntity($id, $template = '');

    /**
     * Render a list of entities
     *
     * @param array     $ids
     * @param string    $template
     * @return string
     */
    abstract public function renderList($ids, $template = '');
}
