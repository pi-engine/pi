<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Form\Factory as FormFactory;
use Module\System\Controller\ComponentController;

/**
 * Cache controller
 *
 * Feature list:
 *
 *  1. List of caches of a section and module
 *  2. Add a custom page to a section and module
 *  3. Delete a custom page
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class CacheController extends ComponentController
{
    /**
     * List of pages sorted by module and section
     */
    public function indexAction()
    {
        // Module name, default as 'system'
        $name = $this->params('name', $this->moduleName('system'));
        if (!$this->permission($name, 'cache')) {
            return;
        }
        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            if (!empty($post['force'])) {
                $id = $post['page'];
                $data = array(
                    'cache_type'    => $post['cache_type'][$id],
                    'cache_ttl'     => $post['cache_ttl'][$id],
                    'cache_level'   => $post['cache_level'][$id],
                );
                Pi::model('page')->update($data, array(
                    'module'    => $name,
                    'section'   => array('front', 'feed')
                ));
            } else {
                $data = array();
                foreach ($post['cache_type'] as $id => $value) {
                    $data[$id] = array('cache_type' => $value);
                }
                foreach ($post['cache_ttl'] as $id => $value) {
                    $data[$id]['cache_ttl'] = $value;
                }
                foreach ($post['cache_level'] as $id => $value) {
                    $data[$id]['cache_level'] = $value;
                }

                foreach ($data as $id => $config) {
                    $row = Pi::model('page')->find($id);
                    if ($row) {
                        $row->assign($config);
                        $row->save();
                    }
                }
            }

            Pi::registry('page_cache')->flush($name);

            $this->jump(
                array('action' => 'index', 'name' => $name),
                _a('Page cache updated successfully.'),
                'success'
            );

            return;
        }

        // Pages of the module
        $select = Pi::model('page')->select()
            ->where(array(
                'module'    => $name,
                'section'   => array('front', 'feed')
            ))
            ->order(array('custom', 'controller', 'action', 'id'));
        $rowset = Pi::model('page')->selectWith($select);
        $sections = array(
            'front' => array(
                'title' => _a('Front'),
                'pages' => array(),
            ),
            'feed'  => array(
                'title' => _a('Feed'),
                'pages' => array(),
            ),
        );

        $factory = new FormFactory;
        $helper = $this->view()->helper('form_select');
        $cacheType = function ($id, $value, $section) use ($factory, $helper) {
            $spec = array(
                'name'          => sprintf('cache_type[%s]', $id),
                'type'          => 'select',
                'attributes'    => array(
                    'options'   => array(
                        'page'      => _a('Page wide'),
                        'action'    => _a('Action data'),
                    ),
                    'value'     => $value ?: 'page',
                    'class'     => 'form-control',
                ),
            );
            if ('feed' == $section) {
                $spec['attributes']['value'] = 'page';
                unset($spec['attributes']['options']['action']);
            }
            $element = $factory->create($spec);
            $content = $helper->render($element);
            return $content;
        };
        $cacheTtl = function ($id, $value) use ($factory, $helper) {
            $element = $factory->create(array(
                'name'          => sprintf('cache_ttl[%s]', $id),
                'type'          => 'cache_ttl',
                'attributes'    => array(
                    'value'     => $value,
                    'class'     => 'form-control',
                ),
            ));
            $content = $helper->render($element);
            return $content;
        };
        $cacheLevel = function ($id, $value) use ($factory, $helper) {
            $element = $factory->create(array(
                'name'          => sprintf('cache_level[%s]', $id),
                'type'          => 'cache_level',
                'attributes'    => array(
                    'value'     => $value,
                    'class'     => 'form-control',
                ),
            ));
            $content = $helper->render($element);
            return $content;
        };

        // Organized pages by section
        $pageModule = array();
        $pageHome   = array();
        foreach ($rowset as $row) {
            $id         = $row->id;
            $section    = $row->section ?: 'front';

            if (!$row->controller) {
                $pageModule[$section] = array(
                    'id'        => $row->id,
                    'title'     => _a('Module wide'),
                    'type'      => $cacheType($id, $row['cache_type'], $section),
                    'ttl'       => $cacheTtl($id, $row['cache_ttl']),
                    'level'     => $cacheLevel($id, $row['cache_level']),
                    'is_module' => true,
                );
                continue;
            } elseif ('index' == $row->controller && 'index' == $row->action) {
                $pageHome[$section] = array(
                    'id'        => $row->id,
                    'title'     => _a('Module home'),
                    'type'      => $cacheType($id, $row['cache_type'], $section),
                    'ttl'       => $cacheTtl($id, $row['cache_ttl']),
                    'level'     => $cacheLevel($id, $row['cache_level']),
                );
                continue;
            }
            $sections[$section]['pages'][] = array(
                'id'        => $row->id,
                'title'     => $row->title,
                'type'      => $cacheType($id, $row['cache_type'], $section),
                'ttl'       => $cacheTtl($id, $row['cache_ttl']),
                'level'     => $cacheLevel($id, $row['cache_level']),
            );
        }
        foreach ($pageHome as $section => $page) {
            array_unshift($sections[$section]['pages'], $page);
        }
        foreach ($pageModule as $section => $page) {
            array_unshift($sections[$section]['pages'], $page);
        }

        $this->view()->assign('pagesBySection', $sections);
        $this->view()->assign('name', $name);
        $this->view()->assign('title', _a('Cache list'));

        $this->view()->setTemplate('cache-list');
    }
}
