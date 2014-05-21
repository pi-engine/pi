<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\Db\RowGateway\RowGateway;

/**
 * Block manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block extends AbstractApi
{
    /** @var string Separator for module block names */
    protected $moduleSeparator = '-';

    /** @var string Module name */
    protected $module = 'system';

    /**
     * Canonize block specs
     *
     * @param array $block
     * @return array
     */
    protected function canonize($block)
    {
        if (isset($block['name']) && empty($block['name'])) {
            $block['name'] = null;
        }
        $root = $block;
        if (isset($root['id'])) {
            unset($root['id']);
        }

        return array($block, $root);
    }

    /**
     * Adds a block and its relevant options, ACL rules
     *
     * @param array $block
     * @return array Root block ID or false, message
     */
    public function add($block)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
            'id'        => 0,
            'root'      => 0,
        );
        list($block, $root) = $this->canonize($block);

        $module = (string) $block['module'];
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');

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
            $block['name'] = $block['name']
                ? $module . $this->moduleSeparator . $block['name'] : null;
        }

        $config = array();
        if (!isset($block['config'])) {
            $block['config'] = array();
        }
        foreach ($block['config'] as $name => $data) {
            $config[$name] = is_scalar($data)
                ? $data
                : (isset($data['value']) ? $data['value'] : '');
        }
        $block['config'] = $config;

        $rowBlock = $modelBlock->createRow($block);
        $rowBlock->save();
        if (!$rowBlock->id) {
            $return['message'] = sprintf(
                'Block view "%s" is not created.',
                $block['name']
            );
            return $return;
        }

        // Build permission rules
        $roles = array('guest', 'member');
        foreach ($roles as $role) {
            Pi::service('permission')->grantPermission($role, array(
                'section'   => 'front',
                'module'    => $module,
                'resource'  => 'block-' . $rowBlock->id
            ));
        }
        $return['status'] = 1;
        $return['id'] = $rowBlock->id;

        return $return;
    }

    /**
     * Updates a block root and its instances and clones
     *
     * If only edit one single instance, use edit()
     *
     * @param int|RowGateway $entity
     * @param array $block
     * @return array  Result pair of status and message
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

        list($block, $root) = $this->canonize($block);

        $configRemove = array();
        $configAdd = array();
        if (isset($block['config'])) {
            /*
            if (!isset($block['config'])) {
                $block['config'] = array();
            }
            */
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
        }

        // Update root
        $rootRow->assign($root);
        try {
            $rootRow->save();
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        $update = array(
            'render'        => isset($block['render']) ? $block['render'] : '',
            'cache_level'   => isset($block['cache_level']) ? $block['cache_level'] : '',
            //'content'       => isset($block['content']) ? $block['content'] : '',
        );

        // Update cloned blocks
        $blockList = $modelBlock->select(array('root' => $rootRow->id));
        foreach ($blockList as $blockRow) {
            $blockRow->assign($update);

            // Update config
            if (isset($block['config'])) {
                $blockConfig = (array) $blockRow->config;
                if ($configRemove) {
                    foreach ($configRemove as $name) {
                        unset($blockConfig[$name]);
                    }
                }
                if ($configAdd) {
                    $blockConfig = array_merge($configAdd, $blockConfig);
                }
                $blockRow->config = $blockConfig;
            }

            try {
                $blockRow->save();
                $status = true;
            } catch (\Exception $e) {
                $status = false;
            }
        }

        // Update template and content for non-cloned blocks
        $updates = array();
        if (isset($block['template'])) {
            $updates['template'] = $block['template'];
        }
        if (isset($block['content'])) {
            $updates['content'] = $block['content'];
        }
        if ($updates) {
            $modelBlock->update($updates, array(
                'root'      => $rootRow->id,
                'cloned'    => 0,
            ));
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
     * @return array Result pair of status and message
     */
    public function delete($entity, $isRoot = false)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
        );
        $modelBlock = Pi::model('block');
        $modelRoot = Pi::model('block_root');
        $modelRule = Pi::model('permission_rule');
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
                $return['message'] = 'Block is not deleted: '
                                   . $e->getMessage();
                return $return;
            }

            // delete from rule table
            try {
                $status = $modelRule->delete(
                    array('resource' => 'block-' . $blockRow->id, 'section' => 'front')
                );
            } catch (\Exception $e) {
                $return['message'] = 'Permission rules are not deleted: ' . $e->getMessage();
                return $return;
            }

            // delete page-block links from page_block table
            $rowsetPage = $modelPageBlock->select(
                array('block' => $blockRow->id)
            );
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
                Pi::registry('block')->flush();
            } else {
                $modules = array();
                foreach (array_keys($pages) as $page) {
                    $row = $modelPage->find($page);
                    $modules[$row->module] = 1;
                }
                foreach (array_keys($modules) as $mod) {
                    Pi::registry('block')->flush($mod);
                }
            }
        }
        $return['status'] = 1;

        return $return;
    }

    /**
     * Update a block instance
     *
     * @param int|RowGateway $entity
     * @param array $block
     * @return array Result pair of status and message
     */
    public function updateBlock($entity, $block)
    {
        $model = Pi::model('block');
        if ($entity instanceof RowGateway) {
            $blockRow = $entity;
        } else {
            $blockRow = $model->find($entity);
        }

        list($block, $root) = $this->canonize($block);

        // Update block
        $blockRow->assign($block);
        try {
            $blockRow->save();
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return array(
            'status'    => 1,
            'message'   => '',
        );
    }
}
