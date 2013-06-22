<?php
/**
 * Pi module installer resource
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
 * @since           3.0
 * @package         Pi\Application
 * @subpackage      Installer
 * @version         $Id$
 */

namespace Pi\Application\Installer\Resource;
use Pi;

/**
 * Block configuration specs
 *
 *  return array(
 *      // Block with renderer and structured options
 *      'blockA' => array(
 *          'title'         => __('Block Title'),       // Required, translated
 *          //'link'          => '/link/to/a/URL',    // Optional
 *          //'class'         => 'css-class',         // Optional, specified stylesheet class for display
 *          'description'   => __('Desribing the block'),   // Optional, translated
 *          'render'        => array('class', 'method'),         // Required
 *          'template'      => 'template', // in module/template/block/, no suffix
 *          'cache_level'   => 'role', // Cache level type, optional: role, locale, user
 *          'access'        => array(), // ACL rules, optional
 *          'config'        => array(
 *              'a' => array(
 *                  'title'         => 'Config A',
 *                  'description'   => 'Config A hint',
 *                  'edit'          => 'select',
 *                  'value'         => 'option_a',
 *                  'edit'          => array(
 *                      'attributes'    => array(
 *                          'type'      => 'select'
 *                          'options'   => array(
 *                              1   => 'Option 1',
 *                              2   => 'Option 2',
 *                              3   => 'Option 3',
 *                          ),
 *                      ),
 *                  ),
 *                  'filter'        => 'num_int',
 *              ),
 *              'b'  => array(
 *                  'title'         => 'Config B',
 *                  'description'   => 'Config B hint',
 *                  'edit'          => 'SpecifiedEditElement',
 *                  'filter'        => 'string',
 *                  'value'         => 'good',
 *              ),
 *              'c'  => array(
 *                  'title'         => 'Config C',
 *                  'description'   => 'Config C hint',
 *                  'edit'          => 'input', // optional
 *                  'filter'        => 'string',
 *                  'value'         => __('sample text'), // translated
 *              ),
 *          ),
 *      ),
 *      ...
 *  );
 */

class Block extends AbstractResource
{
    protected $moduleSeperator = '-';

    protected function canonizeAdd($block)
    {
        $module = $this->event->getParam('module');
        $classPrefix = 'Module\\' . ucfirst($this->event->getParam('directory'));
        if (is_array($block['render'])) {
            $block['render'] = implode('::', $block['render']);
        }
        $block['render'] = $classPrefix . '\\' . ucfirst($block['render']);

        $data = array(
            'name'          => $block['name'],
            'title'         => $block['title'],
            'description'   => isset($block['description']) ? $block['description'] : '',

            'module'        => $module,
            'render'        => $block['render'],
            'template'      => isset($block['template']) ? $block['template'] : '',
            'config'        => isset($block['config']) ? $block['config'] : array(),
            'cache_level'   => isset($block['cache_level']) ? $block['cache_level'] : '',
            'access'        => isset($block['access']) ? $block['access'] : array(),

            //'link'          => isset($block['link']) ? $block['link'] : '',
            //'class'         => isset($block['class']) ? $block['class'] : '',
        );

        return $data;
    }

    protected function canonizeUpdate($block)
    {
        $module = $this->event->getParam('module');
        $classPrefix = 'Module\\' . ucfirst($this->event->getParam('directory'));
        if (is_array($block['render'])) {
            $block['render'] = implode('::', $block['render']);
        }
        $block['render'] = $classPrefix . '\\' . ucfirst($block['render']);

        $data = array(
            'title'         => $block['title'],
            'description'   => isset($block['description']) ? $block['description'] : '',

            'render'        => $block['render'],
            'template'      => isset($block['template']) ? $block['template'] : '',
            'config'        => isset($block['config']) ? $block['config'] : array(),
            'cache_level'   => isset($block['cache_level']) ? $block['cache_level'] : '',
            'access'        => isset($block['access']) ? $block['access'] : array(),

            //'link'          => isset($block['link']) ? $block['link'] : '',
            //'class'         => isset($block['class']) ? $block['class'] : '',
        );

        return $data;
    }

    public function installAction()
    {
        if (empty($this->config)) {
            return;
        }
        $module = $this->event->getParam('module');
        $blocks = $this->config;
        foreach ($blocks as $key => $block) {
            // break the loop if missing block config
            if (empty($block['render'])) {
                continue;
            }
            $block['name'] = $key;
            $block['module'] = $module;
            $data = $this->canonizeAdd($block);
            $message = array();
            $status = $this->addBlock($data, $message);
            if (!$status) {
                $message[] = sprintf('Block "%s" is not created.', $key);
                return array(
                    'status'    => false,
                    'message'   => $message,
                );
            }
        }

        Pi::service('registry')->block->clear($module);
        return true;
    }

    public function updateAction()
    {
        $module = $this->event->getParam('module');
        Pi::service('registry')->block->clear($module);

        if ($this->skipUpgrade()) {
            return;
        }

        $blocks = $this->config ?: array();

        $model = Pi::model('block_root');
        foreach ($blocks as $key => $block) {
            // break the loop if missing block config
            if (empty($block['render'])) {
                continue;
            }
            $block['name'] = $key;
            $block['module'] = $module;
            $rowset = $model->select(array(
                'name'      => $key,
                'module'    => $module,
            ));
            // Add new block
            if (!$rowset->count()) {
                $data = $this->canonizeAdd($block);
                $message = array();
                $status = $this->addBlock($data, $message);
                if (!$status) {
                    $message[] = sprintf('Block "%s" is not created.', $key);
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            // Update existent block
            } else {
                $row = $rowset->current();
                $data = $this->canonizeUpdate($block);
                $message = array();
                $status = $this->updateBlock($row, $data, $message);
                if (!$status) {
                    $message[] = sprintf('Block "%s" is not updated.', $key);
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        // Remove deprecated blocks
        $rowset = $model->select(array('module' => $module));
        foreach ($rowset as $row) {
            if (!isset($blocks[$row->name])) {
                $message = array();
                $status = $this->deleteBlock($row, $message);
                if (!$status) {
                    $message[] = sprintf('Deprecated block "%s" is not updated.', $row->key);
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        return true;
    }

    public function uninstallAction()
    {
        $module = $this->event->getParam('module');

        Pi::model('block')->delete(array('module' => $module));
        Pi::model('block_root')->delete(array('module' => $module));
        /*
        $rowset = Pi::model('block_root')->select(array('module' => $module));
        foreach ($rowset as $row) {
            $message = array();
            $status = $this->deleteBlock($row, $message);
            if (!$status) {
                $message[] = sprintf('Block "%s" is not removed.', $row->key);
                return array(
                    'status'    => false,
                    'message'   => $message,
                );
            }
        }
        */

        Pi::service('registry')->block->clear($module);

        return true;
    }

    public function activateAction()
    {
        $module = $this->event->getParam('module');
        Pi::model('block')->update(array('active' => 1), array('module' => $module));

        Pi::service('registry')->block->clear($module);

        return true;
    }

    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        Pi::model('block')->update(array('active' => 0), array('module' => $module));

        Pi::service('registry')->block->clear($module);

        return true;
    }

    /**
     * Adds a block and its relevant options, ACL rules
     *
     * @param array $block
     * @param string $message
     * @return mixed root block ID or false
     */
    protected function addBlock($block, &$message)
    {
        $result = Pi::service('api')->system(array('block', 'add'), $block);
        extract($result);
        return $status;

        /*
        $module = $block['module'];
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');
        $modelRule = Pi::model('acl_rule');

        $rules = array();
        if (array_key_exists('access', $block)) {
            $rules = $block['access'];
            unset($block['access']);
        }

        // Create block root
        $rowRoot = $modelRoot->createRow($block);
        $rowRoot->save();
        if (!$rowRoot->id) {
            return false;
        }

        // Create block view
        $block['root'] = $rowRoot->id;
        $block['name'] = $module . $this->moduleSeperator . $block['name'];
        $config = array();
        foreach ($block['config'] as $name => $data) {
            $config[$name] = empty($data['value']) ? '' : $data['value'];
        }
        $block['config'] = $config;
        $rowBlock = $modelBlock->createRow($block);
        $rowBlock->save();
        if (!$rowBlock->id) {
            $message[] = sprintf('Block view "%s" is not created.', $block['name']);
            return false;
        }

        // Build ACL rules
        $dataRule = array(
            'resource'  => $rowBlock->id,
            'section'   => 'block',
            'module'    => $module,
        );
        $roles = array('guest', 'member');
        foreach ($roles as $role) {
            $dataRule['role'] = $role;
            if (isset($rules[$role])) {
                $dataRule['deny'] = empty($rules[$role]) ? 1 : 0;
            } else {
                $dataRule['deny'] = 0;
            }
            $status = $modelRule->insert($dataRule);
            if (!$status) {
                $message[] = 'ACL rule is not created';
                return false;
            }
        }

        return true;
        */
    }

    /**
     * Updates a block and its relevant options
     */
    protected function updateBlock($rootRow, $block, &$message)
    {
        //return Pi::service('api')->system->block->update($rootRow, $block, $message);
        $result = Pi::service('api')->system(array('block', 'update'), $rootRow, $block);
        extract($result);
        return $status;

        /*
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');

        $configRemove = array();
        $configAdd = array();
        if ($rootRow->config) {
            foreach ($rootRow->config as $name => $data) {
                if (!isset($block['config'][$name])) {
                    $configRemove[] = $name;
                }
            }
            foreach ($block['config'] as $name => $data) {
                if (!isset($rootRow->config[$name])) {
                    $configAdd[$name] = $data['value'];
                }
            }
        }

        if (array_key_exists('access', $block)) {
            unset($block['access']);
        }

        // Update root
        $rootRow->assign($block);
        $status = $rootRow->save();

        $update = array(
            'render'        => $block['render'],
            'template'      => $block['template'],
            'cache_level'   => $block['cache_level'],
        );
        // Update cloned blocks
        $blockList = $modelBlock->select(array('root' => $rootRow->id));
        foreach ($blockList as $blockRow) {
            $blockRow->assign($update);
            // Update config
            if ($configRemove) {
                foreach ($configRemove as $name) {
                    unset($blockRow->config[$name]);
                }
            }
            if ($configAdd) {
                $blockRow->config = array_merge($configAdd, $blockRow->config);
            }
            $status = $blockRow->save();
        }

        return true;
        */
    }

    /**
     * Deletes a block root and its relevant views, ACL rules
     */
    protected function deleteBlock($rootRow, &$message)
    {
        //return Pi::service('api')->system->block->delete($rootRow, $message);
        $result = Pi::service('api')->system(array('block', 'delete'), $rootRow, true);
        extract($result);
        return $status;

        /*
        $module = $this->event->getParam('module');
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');
        $modelRule = Pi::model('acl_rule');
        $modelPage = Pi::model('page');
        $modelPageBlock = Pi::model('page_block');

        // delete from block table
        try {
            $status = $modelRoot->delete(array('id' => $rootRow->id));
        } catch (\Exception $e) {
            $message[] = 'Block root is not deleted: ' . $e->getMessage();
            return false;
        }

        $rowset = $modelBlock->select(array('root' => $rootRow->id, 'module' => $module));
        foreach ($rowset as $blockRow) {
            try {
                $status = $modelBlock->delete(array('id' => $blockRow->id));
            } catch (\Exception $e) {
                $message[] = 'Block is not deleted: ' . $e->getMessage();
                return false;
            }

            // delete from rule table
            try {
                $status = $modelRule->delete(array('resource' => $blockRow->id, 'section' => 'block'));
            } catch (\Exception $e) {
                $message[] = 'ACL rules are not deleted: ' . $e->getMessage();
                return false;
            }

            // delete page-block links from page_block table
            $rowsetPage = $modelPageBlock->select(array('block' => $blockRow->id));
            $pages = array();
            foreach ($rowsetPage as $row) {
            $pages[$row->page] = 1;
                try {
                    $status = $row->delete();
                } catch (\Exception $e) {
                    $message[] = 'Page-block link is not deleted: ' . $e->getMessage();
                    return false;
                }
            }
            // Clean module block caches
            if (isset($pages[0])) {
                Pi::service('registry')->block->flush();
            } else {
                $modules = array();
                foreach (array_keys($pages) as $page) {
                    $row = $modelPage->find($page);
                    $modules[$row->module] = 1;
                }
                foreach (array_keys($modules) as $mod) {
                    if ($module == $mod) continue;
                    Pi::service('registry')->block->flush($mod);
                }
            }
        }

        return true;
        */
    }
}
