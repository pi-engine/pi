<?php
/**
 * Pi Engine Taxonomy content provider API
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application;

use Zend\View\ViewModel;

abstract class AbstractProvider extends AbstractApi
{
    protected function getRenderer()
    {
        $renderer = Pi::engine()->application()->getServiceManager()->get('viewRenderer');
        return $renderer;
    }

    protected function getViewModel($data = null, $options = null)
    {
        $viewModel = new ViewModel($data, $options);
        return $viewModel;
    }

    public function isActive()
    {
        return Pi::service('module')->isActive($this->module);
    }

    abstract public function hasEntity($id);
    abstract public function getEntity($id);
    abstract public function getList($id, $cols = null);
    abstract public function renderEntity($id, $template = '');
    abstract public function renderList($ids, $template = '');
}
