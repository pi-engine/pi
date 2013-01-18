<?php
/**
 * System module block manipulation API class
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
 * @package         Module\System
 * @version         $Id$
 */

namespace Module\System\Api;

use Pi;
use Pi\Application\AbstractApi;
use Pi\Db\RowGateway\RowGateway;
use Pi\Acl\Acl as AclHandler;

class Block extends AbstractApi
{
    protected $moduleSeperator = '-';
    protected $module = 'system';

    protected $blockColumns = array(
        'id',
        'module', 'name', 'title', 'description', 'render', 'template', 'config', 'cache_level', 'type',
        'root', 'cache_ttl', 'content', 'subline', 'class', 'title_hidden', 'active', 'cloned'
    );
    protected $rootColumns = array(
        'module', 'name', 'title', 'description', 'render', 'template', 'config', 'cache_level', 'type',
    );

    protected function canonize($block)
    {
        $root = array();
        $access = array();

        if (isset($block['name']) && empty($block['name'])) {
            $block['name'] = null;
        }
        if (isset($block['access'])) {
            $access = $block['access'];
            unset($block['access']);
        }
        foreach (array_keys($block) as $key) {
            if (in_array($key, $this->rootColumns)) {
                $root[$key] = $block[$key];
            }
            if (!in_array($key, $this->blockColumns)) {
                unset($block[$key]);
            }
        }

        return array($block, $root, $access);
    }

    /**
     * Adds a block and its relevant options, ACL rules
     *
     * @param array $block
     * @return array root block ID or false, message
     */
    public function add($block)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
            'id'        => 0,
            'root'      => 0,
        );
        list($block, $root, $access) = $this->canonize($block);

        $module = (string) $block['module'];
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');
        $modelRule = Pi::model('acl_rule');

        // Create block root for module block
        if ($module && empty($block['root'])) {
            if (!isset($block['root']) || false !== $block['root']) {
                // Create block root
                $rowRoot = $modelRoot->createRow($root);
                $rowRoot->save();
                if (!$rowRoot->id) {
                    return $return;
                }
                $return['root'] = $rowRoot->id;
                $block['root'] = $rowRoot->id;
            }

            // Create block view
            $block['name'] = $block['name'] ? $module . $this->moduleSeperator . $block['name'] : null;
        }


        $config = array();
        if (!isset($block['config'])) {
            $block['config'] = array();
        }
        foreach ($block['config'] as $name => $data) {
            $config[$name] = is_scalar($data) ? $data : (isset($data['value']) ? $data['value'] : '');
        }
        $block['config'] = $config;


        $rowBlock = $modelBlock->createRow($block);
        $rowBlock->save();
        if (!$rowBlock->id) {
            $return['message'] = sprintf('Block view "%s" is not created.', $block['name']);
            return $return;
        }

        // Build ACL rules
        $dataRule = array(
            'resource'  => $rowBlock->id,
            'section'   => 'block',
            'module'    => $module,
        );
        $roles = array('guest', 'member');
        foreach ($roles as $role) {
            $rule = isset($access[$role]) ? $access[$role] : 1;
            AclHandler::addRule($rule, $role, 'block', $rowBlock->id, $module);
            /*
            $dataRule['role'] = $role;
            if (isset($access[$role])) {
                $dataRule['deny'] = empty($access[$role]) ? 1 : 0;
            } else {
                $dataRule['deny'] = 0;
            }
            $status = $modelRule->insert($dataRule);
            if (!$status) {
                $return['message'] = 'ACL rule is not created';
                return $return;
            }
            */
        }
        $return['status'] = 1;
        $return['id'] = $rowBlock->id;

        return $return;
    }

    /**
     * Updates a block root and its entities
     *
     * @param int|RowGateway $entity
     * @param array $block
     * @return array bool, message
     */
    public function update($entity, $block)
    {
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');
        if ($entity instanceof RowGateway) {
            $rootRow = $entity;
        } else {
            $rootRow = $modelRoot->find($entity);
        }

        list($block, $root, $access) = $this->canonize($block);

        $configRemove = array();
        $configAdd = array();
        if (!isset($block['config'])) {
            $block['config'] = array();
        }
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

        // Update root
        $rootRow->assign($root);
        $status = $rootRow->save();

        $update = array(
            'render'        => isset($block['render']) ? $block['render'] : '',
            'template'      => isset($block['template']) ? $block['template'] : '',
            'cache_level'   => isset($block['cache_level']) ? $block['cache_level'] : '',
            'content'       => isset($block['content']) ? $block['content'] : '',
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

        return array(
            'status'    => 1,
            'message'   => '',
        );
    }

    /**
     * Deletes a block and its relevant views, ACL rules
     *
     * @param int|RowGateway $entity
     * @param bool $isRoot
     * @return array bool, message
     */
    public function delete($entity, $isRoot = false)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
        );
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');
        $modelRule = Pi::model('acl_rule');
        $modelPage = Pi::model('page');
        $modelPageBlock = Pi::model('page_block');

        if ($entity instanceof RowGateway) {
            $isRoot = ($entity->module && !$entity->root) ? true : false;
            if ($isRoot) {
                $rootId = $entity->id;
            }
        } elseif ($isRoot) {
            $rootId = $entity;
        }

        if ($isRoot) {
            // delete root from block_root table
            try {
                $status = $modelRoot->delete(array('id' => $rootId));
            } catch (\Exception $e) {
                $return['message'] = 'Block root is not deleted: ' . $e->getMessage();
                return $return;
            }

            $rowset = $modelBlock->select(array('root' => $rootId));
        } elseif ($entity instanceof RowGateway) {
            $rowset = array($entity);
        } else {
            $rowset = $modelBlock->select(array('id' => $entity));
        }

        foreach ($rowset as $blockRow) {
            try {
                $status = $modelBlock->delete(array('id' => $blockRow->id));
            } catch (\Exception $e) {
                $return['message'] = 'Block is not deleted: ' . $e->getMessage();
                return $return;
            }

            // delete from rule table
            try {
                $status = $modelRule->delete(array('resource' => $blockRow->id, 'section' => 'block'));
            } catch (\Exception $e) {
                $return['message'] = 'ACL rules are not deleted: ' . $e->getMessage();
                return $return;
            }

            // delete page-block links from page_block table
            $rowsetPage = $modelPageBlock->select(array('block' => $blockRow->id));
            $pages = array();
            foreach ($rowsetPage as $row) {
                $pages[$row->page] = 1;
                try {
                    $status = $row->delete();
                } catch (\Exception $e) {
                    $return['message'] = 'Page-block link is not deleted: ' . $e->getMessage();
                    return $return;
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
        $return['status'] = 1;

        return $return;
    }

    /**
     * Edit a block
     *
     * @param int|RowGateway $entity
     * @param array $block
     * @return array bool, message
     */
    public function edit($entity, $block)
    {
        $model = Pi::model('block');
        if ($entity instanceof RowGateway) {
            $blockRow = $entity;
        } else {
            $blockRow = $model->find($entity);
        }

        //$block = $this->canonize($block);
        list($block, $root, $access) = $this->canonize($block);

        // Update root
        $blockRow->assign($block);
        $status = $blockRow->save();

        return array(
            'status'    => 1,
            'message'   => '',
        );
    }
}
